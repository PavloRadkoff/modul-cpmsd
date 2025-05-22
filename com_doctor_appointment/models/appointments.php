```php
<?php
/**
 * @package     COM_DOCTOR_APPOINTMENTS
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class DoctorAppointmentsModelAppointments extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'doctor_id', 'a.doctor_id',
                'patient_name', 'a.patient_name',
                'appointment_datetime', 'a.appointment_datetime',
                'status', 'a.status',
                'callcenter_status_id', 'a.callcenter_status_id'
            ];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('a.*')
              ->select($db->quoteName('d.name', 'doctor_name'))
              ->select($db->quoteName('cs.status_name', 'callcenter_status'))
              ->from($db->quoteName('#__doctor_appointments', 'a'))
              ->join('LEFT', $db->quoteName('#__doctors', 'd') . ' ON ' . $db->quoteName('a.doctor_id') . ' = ' . $db->quoteName('d.id'))
              ->join('LEFT', $db->quoteName('#__callcenter_statuses', 'cs') . ' ON ' . $db->quoteName('a.callcenter_status_id') . ' = ' . $db->quoteName('cs.id'));

        // Фільтри
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . $search . '%');
            $query->where('(' . $db->quoteName('a.patient_name') . ' LIKE ' . $search . ' OR ' . $db->quoteName('a.patient_phone') . ' LIKE ' . $search . ')');
        }

        $orderCol = $this->state->get('list.ordering', 'a.appointment_datetime');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
```