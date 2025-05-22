<?php
/** 
 * @package     COM_DOCTOR_APPOINTMENTS
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.framework');

$this->items = $this->get('Items');
?>

<div class="com-doctor-appointments">
    <h2><?php echo Text::_('COM_DOCTOR_APPOINTMENTS_APPOINTMENTS'); ?></h2>
    <?php if (empty($this->items)): ?>
        <p><?php echo Text::_('COM_DOCTOR_APPOINTMENTS_NO_APPOINTMENTS'); ?></p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?php echo Text::_('COM_DOCTOR_APPOINTMENTS_TABLE_PATIENT_NAME'); ?></th>
                    <th><?php echo Text::_('COM_DOCTOR_APPOINTMENTS_TABLE_DOCTOR'); ?></th>
                    <th><?php echo Text::_('COM_DOCTOR_APPOINTMENTS_TABLE_DATETIME'); ?></th>
                    <th><?php echo Text::_('COM_DOCTOR_APPOINTMENTS_TABLE_STATUS'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item->patient_name); ?></td>
                        <td><?php echo htmlspecialchars($item->doctor_name); ?></td>
                        <td><?php echo htmlspecialchars($item->appointment_datetime); ?></td>
                        <td><?php echo htmlspecialchars($item->status); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>