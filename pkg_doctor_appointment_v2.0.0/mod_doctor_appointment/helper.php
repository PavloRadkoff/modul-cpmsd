```php
<?php
/**
 * @package     MOD_DOCTOR_APPOINTMENT
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

class ModDoctorAppointmentHelper
{
    /**
     * Отримує список лікарів
     *
     * @return  array  Масив об'єктів лікарів
     */
    public static function getDoctors(): array
    {
        $db = Factory::getContainer()->get(DatabaseDriver::class);
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__doctors'))
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);
        try {
            return $db->loadObjectList();
        } catch (\Exception $e) {
            Log::add('Error fetching doctors: ' . $e->getMessage(), Log::ERROR, 'mod_doctor_appointment');
            return [];
        }
    }

    /**
     * Отримує список доступних слотів для конкретного лікаря
     *
     * @param   \Joomla\Registry\Registry  $params    Параметри модуля
     * @param   int                        $doctorId  ID лікаря
     *
     * @return  array  Масив доступних слотів, згрупованих по днях
     */
    public static function getAvailableSlotsByDoctor(\Joomla\Registry\Registry $params, int $doctorId): array
    {
        $db = Factory::getContainer()->get(DatabaseDriver::class);
        $query = $db->getQuery(true);

        $availableDays = $params->get('available_days', [1, 2, 3, 4, 5]);
        $startTimeStr = $params->get('start_time', '09:00');
        $endTimeStr = $params->get('end_time', '17:00');
        $slotDuration = (int) $params->get('slot_duration', 30);
        $daysInAdvance = (int) $params->get('days_in_advance', 7);
        $timeZone = Factory::getApplication()->get('offset');

        $availableSlots = [];
        $now = new Date('now', $timeZone);
        $today = new Date('today', $timeZone);

        // Отримуємо заброньовані слоти для лікаря
        $endDateLimit = (clone $today)->modify('+' . ($daysInAdvance + 1) . ' days');
        $query->select($db->quoteName('appointment_datetime'))
              ->from($db->quoteName('#__doctor_appointments'))
              ->where($db->quoteName('doctor_id') . ' = ' . (int) $doctorId)
              ->where($db->quoteName('appointment_datetime') . ' >= ' . $db->quote($now->toSql()))
              ->where($db->quoteName('appointment_datetime') . ' < ' . $db->quote($endDateLimit->toSql()))
              ->where($db->quoteName('status') . ' != ' . $db->quote('cancelled'));

        $db->setQuery($query);
        try {
            $bookedSlotsTimes = $db->loadColumn();
        } catch (\Exception $e) {
            Log::add('Error fetching booked slots: ' . $e->getMessage(), Log::ERROR, 'mod_doctor_appointment');
            return [];
        }

        $bookedSlots = [];
        foreach ($bookedSlotsTimes as $timeStr) {
            try {
                $bookedDate = new Date($timeStr, 'UTC');
                $bookedDate->setTimezone(new DateTimeZone($timeZone));
                $bookedSlots[$bookedDate->format('Y-m-d H:i')] = true;
            } catch (\Exception $e) {
                Log::add('Error parsing booked slot time: ' . $timeStr, Log::WARNING, 'mod_doctor_appointment');
            }
        }

        // Генеруємо слоти
        for ($i = 0; $i < $daysInAdvance; $i++) {
            $day = (clone $today)->modify("+$i days");
            $dayOfWeek = (int) $day->format('w');

            if (!in_array($dayOfWeek, $availableDays)) {
                continue;
            }

            $dayStr = $day->format('Y-m-d');
            $daySlots = [];

            try {
                $slotTime = new Date($dayStr . ' ' . $startTimeStr, $timeZone);
                $endTime = new Date($dayStr . ' ' . $endTimeStr, $timeZone);

                while ($slotTime < $endTime) {
                    if ($slotTime > $now) {
                        $slotDateTimeStr = $slotTime->format('Ym-d H:i');
                        if (!isset($bookedSlots[$slotDateTimeStr])) {
                            $daySlots[] = clone $slotTime;
                        }
                    }
                    $slotTime->modify('+' . $slotDuration . ' minutes');
                }
            } catch (\Exception $e) {
                Log::add('Error generating slots for ' . $dayStr . ': ' . $e->getMessage(), Log::ERROR, 'mod_doctor_appointment');
                continue;
            }

            if (!empty($daySlots)) {
                $availableSlots[$dayStr] = $daySlots;
            }
        }

        return $availableSlots;
    }

    /**
     * Зберігає новий запис
     *
     * @param   array                      $data    Дані з форми
     * @param   \Joomla\Registry\Registry  $params  Параметри модуля
     *
     * @return  bool
     */
    public static function saveAppointment(array $data, \Joomla\Registry\Registry $params): bool
    {
        $db = Factory::getContainer()->get(DatabaseDriver::class);
        $timeZone = Factory::getApplication()->get('offset');

        if (empty($data['name']) || empty($data['phone']) || empty($data['slot']) || empty($data['doctor_id'])) {
            Log::add('Save attempt with missing required fields.', Log::WARNING, 'mod_doctor_appointment');
            return false;
        }

        try {
            $appointmentDate = new Date($data['slot'], $timeZone);
            $now = new Date('now', $timeZone);
            if ($appointmentDate <= $now) {
                Factory::getApplication()->enqueueMessage(Text::_('MOD_DOCTOR_APPOINTMENT_ERROR_PAST_SLOT'), 'error');
                return false;
            }
            if (!self::isSlotWithinSchedule($appointmentDate, $params)) {
                Factory::getApplication()->enqueueMessage(Text::_('MOD_DOCTOR_APPOINTMENT_ERROR_OUTSIDE_SCHEDULE'), 'error');
                return false;
            }
        } catch (\Exception $e) {
            Log::add('Invalid slot format: ' . $data['slot'], Log::ERROR, 'mod_doctor_appointment');
            return false;
        }

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__doctor_appointments'))
            ->where($db->quoteName('appointment_datetime') . ' = ' . $db->quote($appointmentDate->toSql(true)))
            ->where($db->quoteName('doctor_id') . ' = ' . (int) $data['doctor_id'])
            ->where($db->quoteName('status') . ' != ' . $db->quote('cancelled'));

        $db->setQuery($query);
        try {
            if ((int) $db->loadResult() > 0) {
                Factory::getApplication()->enqueueMessage(Text::_('MOD_DOCTOR_APPOINTMENT_ERROR_SLOT_TAKEN'), 'error');
                return false;
            }
        } catch (\Exception $e) {
            Log::add('Database error checking slot: ' . $e->getMessage(), Log::ERROR, 'mod_doctor_appointment');
            return false;
        }

        $appointment = new \stdClass();
        $appointment->doctor_id = (int) $data['doctor_id'];
        $appointment->patient_name = trim($data['name']);
        $appointment->patient_phone = trim($data['phone']);
        $appointment->patient_email = !empty($data['email']) ? trim($data['email']) : null;
        $appointment->appointment_datetime = $appointmentDate->toSql(true);
        $appointment->status = 'pending';

        try {
            return (bool) $db->insertObject('#__doctor_appointments', $appointment, 'id');
        } catch (\Exception $e) {
            Log::add('Database error saving appointment: ' . $e->getMessage(), Log::ERROR, 'mod_doctor_appointment');
            return false;
        }
    }

    /**
     * Перевіряє, чи час відповідає графіку
     *
     * @param   Date                       $dateTimeToCheck  Час для перевірки
     * @param   \Joomla\Registry\Registry  $params           Параметри модуля
     *
     * @return  bool
     */
    private static function isSlotWithinSchedule(Date $dateTimeToCheck, \Joomla\Registry\Registry $params): bool
    {
        $availableDays = $params->get('available_days', [1, 2, 3, 4, 5]);
        $startTimeStr = $params->get('start_time', '09:00');
        $endTimeStr = $params->get('end_time', '17:00');
        $slotDuration = (int) $params->get('slot_duration', 30);
        $timeZone = $dateTimeToCheck->getTimezone();

        $dayOfWeek = (int) $dateTimeToCheck->format('w');
        if (!in_array($dayOfWeek, $availableDays)) {
            return false;
        }

        try {
            $dayStr = $dateTimeToCheck->format('Y-m-d');
            $slotStartTime = new Date($dayStr . ' ' . $startTimeStr, $timeZone);
            $slotEndTimeAllowed = new Date($dayStr . ' ' . $endTimeStr, $timeZone);

            if ($dateTimeToCheck >= $slotStartTime && $dateTimeToCheck < $slotEndTimeAllowed) {
                $startOfDayTime = new Date($dayStr . ' ' . $startTimeStr, $timeZone);
                $diffInMinutes = ($dateTimeToCheck->toUnix() - $startOfDayTime->toUnix()) / 60;
                if (abs(round($diffInMinutes / $slotDuration) - ($diffInMinutes / $slotDuration)) < 0.001) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            Log::add('Error in isSlotWithinSchedule: ' . $e->getMessage(), Log::ERROR, 'mod_doctor_appointment');
            return false;
        }

        return false;
    }
}
```