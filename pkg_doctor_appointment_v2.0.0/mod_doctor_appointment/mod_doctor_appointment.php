```php
<?php
/**
 * @package     MOD_DOCTOR_APPOINTMENT
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

require_once __DIR__ . '/helper.php';

$app = Factory::getApplication();
$input = $app->input;
$module = $module;
$params = $params;

$layout = $params->get('layout', 'default');
$result = null;

// Обробка POST-запиту
if ($input->getMethod() === 'POST' && $input->getCmd('task') === 'submit_appointment') {
    if (!Session::checkToken('post')) {
        $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
        $app->redirect(Uri::getInstance()->toString());
        return;
    }

    $data = [
        'doctor_id' => $input->getInt('appointment_doctor', 0),
        'name' => $input->getString('appointment_name', ''),
        'phone' => $input->getString('appointment_phone', ''),
        'email' => $input->getString('appointment_email', ''),
        'slot' => $input->getString('appointment_slot', '')
    ];

    if (empty($data['doctor_id']) || empty($data['name']) || empty($data['phone']) || empty($data['slot'])) {
        $result = ['success' => false, 'message' => Text::_('MOD_DOCTOR_APPOINTMENT_ERROR_REQUIRED_FIELDS')];
    } elseif ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $result = ['success' => false, 'message' => Text::_('MOD_DOCTOR_APPOINTMENT_ERROR_INVALID_EMAIL')];
    } else {
        $saveResult = ModDoctorAppointmentHelper::saveAppointment($data, $params);
        $result = $saveResult
            ? ['success' => true, 'message' => Text::_('MOD_DOCTOR_APPOINTMENT_SUCCESS_MESSAGE')]
            : ['success' => false, 'message' => Text::_('MOD_DOCTOR_APPOINTMENT_ERROR_SAVING')];
    }

    $app->setUserState('mod_doctor_appointment.result', $result);
    $app->redirect(Uri::getInstance()->toString());
    return;
}

// Отримання лікарів
$doctors = ModDoctorAppointmentHelper::getDoctors();

// Отримання слотів для обраного лікаря (за замовчуванням перший лікар)
$doctorId = $input->getInt('doctor_id', $doctors[0]->id ?? 0);
$availableSlots = $doctorId ? ModDoctorAppointmentHelper::getAvailableSlotsByDoctor($params, $doctorId) : [];

$sessionResult = $app->getUserState('mod_doctor_appointment.result', null);
if ($sessionResult) {
    $result = $sessionResult;
    $app->setUserState('mod_doctor_appointment.result', null);
}

require ModuleHelper::getLayoutPath('mod_doctor_appointment', $layout);
```