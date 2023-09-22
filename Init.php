<?php
/**
 * This file is part of Anticipos plugin for FacturaScripts
 * Copyright (C) 2022 Carlos Garcia Gomez <carlos@facturascripts.com>
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

use FacturaScripts\Core\Base\InitClass;
use FacturaScripts\Core\Base\ToolBox;
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
		
        if (class_exists('\\FacturaScripts\\Dinamic\\Model\\Proyecto')) {
            $this->loadExtension(new Extension\Controller\EditProyecto());
        }

        // export manager
        ExportManager::addOptionModel('PDFanticiposExport', 'PDF', 'Anticipo');
        ExportManager::addOptionModel('MAILanticiposExport', 'MAIL', 'Anticipo');
    }

    public function update()
    {
        $this->setupSettings();
        $this->updateEmailNotifications();
    }

    private function setupSettings()
    {
        $appsettings = $this->toolBox()->appSettings();
        if (empty($appsettings->get('anticipos', 'pdAnticipos'))) {
            $appsettings->set('anticipos', 'pdAnticipos', false);
        }
        if (empty($appsettings->get('anticipos', 'level'))) {
            $appsettings->set('anticipos', 'level', 20);
        }
        $appsettings->save();
    }

    private function updateEmailNotifications()
    {
        $i18n = ToolBox::i18n();
        $notificationModel = new EmailNotification();
        $keys = [
            'sendmail-Anticipo'
        ];
        foreach ($keys as $key) {
            if ($notificationModel->loadFromCode($key)) {
                continue;
            }

            $notificationModel->name = $key;
            $notificationModel->body = $i18n->trans('sendmail-anticipo-body');
            $notificationModel->subject = $i18n->trans('sendmail-anticipo-subject');
            $notificationModel->enabled = true;
            $notificationModel->save();
        }
    }
}
