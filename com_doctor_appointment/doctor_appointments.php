<?php
/** 
 * @package     COM_DOCTOR_APPOINTMENTS
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class DoctorAppointmentsComponent extends JControllerLegacy
{
    public function display($cachable = false, $urlparams = [])
    {
        $app = Factory::getApplication();
        $view = $app->input->getCmd('view', 'appointments');
        $app->input->set('view', $view);

        parent::display($cachable, $urlparams);
        return $this;
    }
}