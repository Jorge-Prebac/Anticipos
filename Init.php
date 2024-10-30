<?php
/**
 * This file is part of Anticipos plugin for FacturaScripts
 * Copyright (C) 2024 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Plugins\Anticipos;

use FacturaScripts\Core\Plugins;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Base\InitClass;
use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Dinamic\Lib\ExportManager;
use FacturaScripts\Dinamic\Model\EmailNotification;

/**
 * Description of Init
 *
 * @author Jorge-Prebac <info@prebac.com>
 */
class Init extends InitClass
{

    public function init()
    {
        $this->loadExtension(new Extension\Controller\EditCliente());
        $this->loadExtension(new Extension\Controller\EditPresupuestoCliente());
        $this->loadExtension(new Extension\Controller\EditPedidoCliente());
        $this->loadExtension(new Extension\Controller\EditAlbaranCliente());
        $this->loadExtension(new Extension\Controller\EditFacturaCliente());

		$this->loadExtension(new Extension\Controller\ListPresupuestoCliente());
		$this->loadExtension(new Extension\Controller\ListPedidoCliente());
		$this->loadExtension(new Extension\Controller\ListAlbaranCliente());
		$this->loadExtension(new Extension\Controller\ListFacturaCliente());

        $this->loadExtension(new Extension\Controller\EditProveedor());
        $this->loadExtension(new Extension\Controller\EditPresupuestoProveedor());
        $this->loadExtension(new Extension\Controller\EditPedidoProveedor());
        $this->loadExtension(new Extension\Controller\EditAlbaranProveedor());
        $this->loadExtension(new Extension\Controller\EditFacturaProveedor());
		
		$this->loadExtension(new Extension\Controller\ListPresupuestoProveedor());
		$this->loadExtension(new Extension\Controller\ListPedidoProveedor());
		$this->loadExtension(new Extension\Controller\ListAlbaranProveedor());
		$this->loadExtension(new Extension\Controller\ListFacturaProveedor());

		$this->loadExtension(new Extension\Lib\BusinessDocumentGenerator());
		
		if (Plugins::isEnabled('Proyectos')) {
			$this->loadExtension(new Extension\Controller\EditProyecto());
		}

        // export manager
        ExportManager::addOptionModel('PDFanticiposExport', 'PDF', 'Anticipo');
        ExportManager::addOptionModel('MAILanticiposExport', 'MAIL', 'Anticipo');
		
		ExportManager::addOptionModel('PDFanticiposExport', 'PDF', 'AnticipoP');
		ExportManager::addOptionModel('MAILanticiposExport', 'MAIL', 'AnticipoP');
    }

    public function update()
    {
		$Tables = array("anticiposp", "anticipos");
		foreach ($Tables as $Table) {
			$this->updateUserToNick($Table);
		}
        $this->setupSettings();
        $this->updateEmailNotifications();
    }

	private function setupSettings()
	{
		if (empty(Tools::settings('anticipos', 'pdAnticipos'))) {
			Tools::settingsSet('anticipos', 'pdAnticipos', false);
		}
		if (empty(Tools::settings('anticipos', 'level'))) {
			Tools::settingsSet('anticipos', 'level', 20);
		}
		Tools::settingsSave();
	}

    private function updateEmailNotifications() : void
    {
        $notificationModel = new EmailNotification();
        $keys = [
            'sendmail-Anticipo'
        ];
        foreach ($keys as $key) {
            if ($notificationModel->loadFromCode($key)) {
                continue;
            }

            $notificationModel->name = $key;

			$notificationModel->body = Tools::lang()->trans($key . '-body');
			$notificationModel->subject = Tools::lang()->trans($key);

            $notificationModel->enabled = true;
            $notificationModel->save();
        }
    }

	protected function updateUserToNick($Table)
	{
		$dataBase = new DataBase();

		// Comprobamos si se ha encontrado la columna "user" en la tabla
		$sql = "SELECT column_name 
				FROM information_schema.columns 
				WHERE table_name = '" . $Table . "' 
				AND column_name = 'user';";

		$resultado = $dataBase->select($sql);

		/*	Cuando NO está vacío el Array $resultado, realiza el proceso de
		cambiar el nombre de la columna USER por NICK */
		if (!empty($resultado) && isset($resultado[0]['column_name'])) {
			Tools::Log()->info('La columna USER existe en la tabla ' . $Table . '. Procediendo a renombrar.');

			// Cambiamos el nombre de la columna USER por el de NICK
			$sql = FS_DB_TYPE == 'postgresql'
            ? "ALTER TABLE \" . $Table .  \" RENAME COLUMN \"user\" TO \"nick\";"
            : "ALTER TABLE `" . $Table . "` CHANGE `user` `nick` VARCHAR(50);";

			if (false === ($dataBase->exec($sql))) {
				Tools::Log()->warning('Error al cambiar la columna USER por NICK en la tabla ' . $Table);
			} else {
				Tools::Log()->info('Se ha cambiado el nombre de la columna USER por el de NICK con éxito en la tabla ' . $Table);
			}
		} else {
			Tools::Log()->info('No existe la columna USER en la tabla ' . $Table .  '!!!, contacte con el desarrollador del plugin');
		}
	}
}
