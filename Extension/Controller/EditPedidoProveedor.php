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

namespace FacturaScripts\Plugins\Anticipos\Extension\Controller;

use Closure;
use FacturaScripts\Core\Session;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of EditPedidoProveedor
 *
 * @author Jorge-Prebac <info@prebac.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */

class EditPedidoProveedor
{
	protected function createViews(): Closure
	{
		return function() {
			$user = Session::get('user');
			if (!false == $user->can('ListAnticipoP')) {
				//el usuario tiene acceso
				$this->createViewsListAnticipoP();
			}
		};
	}
	
	protected function createViewsListAnticipoP($viewName = 'ListAnticipoP')
	{
		return function() {
			$viewName = 'ListAnticipoP';
			$this->addListView($viewName, 'AnticipoP', 'supplier-advance-payments', 'fas fa-donate');
			$this->views[$viewName]->addOrderBy(['fecha'], 'date', 2);
			$this->views[$viewName]->addOrderBy(['fase'], 'phase');
			$this->views[$viewName]->addOrderBy(['importe'], 'amount');
		};
	}

    public function loadData(): Closure
	{
        return function($viewName, $view) {
            if ($viewName === 'ListAnticipoP') {
				$codigo = $this->getViewModelValue($this->getMainViewName(), 'idpedido');
				$codproveedor = $this->getViewModelValue($this->getMainViewName(), 'codproveedor');
                $where = [
					new DataBaseWhere('idpedido', $codigo),
					new DataBaseWhere('codproveedor', $codproveedor, '=', 'AND'),
				];
                $view->loadData('', $where);

				if (empty ($this->views[$viewName]->model->idempresa)) {
					$idempresa = $this->getViewModelValue($this->getMainViewName(), 'idempresa');
					$where = [
						new DataBaseWhere('idempresa', null),
						new DataBaseWhere('idempresa', $idempresa, '=', 'OR'),
					];
					$view->loadData('', $where);
				}

				// si está instalado el plugin Proyectos añadimos el idproyecto del documento
				if (true === class_exists('\\FacturaScripts\\Dinamic\\Model\\Proyecto')) {
					if (empty ($this->views[$viewName]->model->idproyecto)) {
						$idproyecto = $this->getViewModelValue($this->getMainViewName(), 'idproyecto');
						$where = [
							new DataBaseWhere('idproyecto', null),
							new DataBaseWhere('idproyecto', null, 'IS NOT', 'OR'),
							new DataBaseWhere('idproyecto', $idproyecto, '=', 'OR'),
						];
						$view->loadData('', $where);
					}
				}

				if (!$this->getViewModelValue($this->getMainViewName(), 'editable')) {
					$this->setSettings($viewName, 'btnDelete', false);
					$this->setSettings($viewName, 'btnNew', false);
					$this->setSettings($viewName, 'checkBoxes', false);
				}
			}
		};
    }
}