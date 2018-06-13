<?php

	/* This file is part of Jeedom.
	 *
	 * Jeedom is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * Jeedom is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
	 */

	require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

	function sigri_linky_install() {
	  $random_minutes = rand(1, 59);
	  $crontab_schedule = $random_minutes." 4-23/5 * * *";
	  $cron = cron::byClassAndFunction('sigri_linky', 'launch_sigri_linky');
	  if (!is_object($cron)) {
		$cron = new cron();
		$cron->setClass('sigri_linky');
		$cron->setFunction('launch_sigri_linky');
		$cron->setEnable(1);
		$cron->setDeamon(0);
		$cron->setSchedule($crontab_schedule);
		$cron->save();
	  }
	}

	function sigri_linky_update() {
	  $random_minutes = rand(1, 59);
	  $crontab_schedule = $random_minutes." */6 * * *";
	  $cron = cron::byClassAndFunction('sigri_linky', 'launch_sigri_linky');
	  if (!is_object($cron)) {
		$cron = new cron();
		$cron->setClass('sigri_linky');
		$cron->setFunction('launch_sigri_linky');
		$cron->setEnable(1);
		$cron->setDeamon(0);
		$cron->setSchedule($crontab_schedule);
		$cron->save();
	  }
	  $cron->setSchedule($crontab_schedule);
	  $cron->save();
	  $cron->stop();
	}

	function sigri_linky_remove() {
	  $cron = cron::byClassAndFunction('sigri_linky', 'launch_sigri_linky');
	  if (is_object($cron)) {
		$cron->stop();
		$cron->remove();
	  }
	}
?>