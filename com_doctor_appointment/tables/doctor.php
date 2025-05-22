<?php
/** 
 * @package     COM_DOCTOR_APPOINTMENTS
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class DoctorAppointmentsTableDoctor extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__doctors', 'id', $db);
    }
}