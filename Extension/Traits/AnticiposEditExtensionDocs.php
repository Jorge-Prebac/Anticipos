<?php
/**
 * This file is part of Anticipos plugin for FacturaScripts
 * Copyright (C) 2025 Carlos Garcia Gomez <carlos@facturascripts.com>
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

namespace FacturaScripts\Plugins\Anticipos\Extension\Traits;

use Closure;
use FacturaScripts\Core\Plugins;
use FacturaScripts\Core\Session;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\Anticipo;
use FacturaScripts\Dinamic\Model\AnticipoP;

///use FacturaScripts\Core\Lib\ExtendedController\EditController;

/**
 * Description of AnticiposEditExtensionDocs
 *
 * @author Jorge-Prebac <info@smartcuines.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
 
trait AnticiposEditExtensionDocs
{
	protected function createViews(): Closure
	{
		return function() {
			$model = $this->getModel();
			if ($model->subjectColumn() === 'codproveedor') {
				$this->createViewsListAnticipo('ListAnticipoP', 'AnticipoP');
			} else {
				$this->createViewsListAnticipo('ListAnticipo', 'Anticipo');
			}
		};
	}

	protected function createViewsListAnticipo($viewName = 'string', $mdlAnticipo = 'string')
	{
		return function($viewName, $mdlAnticipo) {

			$user = Session::get('user');
			if (false == $user->can($viewName)) {
				return;
			}

			$this->addListView($viewName, $mdlAnticipo, 'advance-payments', 'fa-solid fa-donate')
				->addOrderBy(['fecha'], 'date', 2)
				->addOrderBy(['fase'], 'phase')
				->addOrderBy(['importe'], 'amount');

				// si NO está activado el plugin Proyectos, desactivamos su columna
				if (false === Plugins::isEnabled('Proyectos')) {
					$this->views[$viewName]->disableColumn('project');
					$this->views[$viewName]->disableColumn('project-total-amount');
				}
		};
	}

    public function loadData(): Closure
	{
        return function($viewName, $view) {

			switch ($viewName) {

				case 'ListAnticipoP':
				case 'ListAnticipo':

					$model = $this->getModel();
					$modelName = $model->modelClassName();
					$modelpc = $model->primaryColumn();
					$codigo = $model->id();
					
					if ($model->subjectColumn() === 'codproveedor') {
						$subject = 'codproveedor';
						$idSubject = $model->codproveedor;
					} else {
						$subject = 'codcliente';
						$idSubject = $model->codcliente;
					}
					$where = [
						new DataBaseWhere($modelpc, $codigo),
						new DataBaseWhere($subject, $idSubject, '=', 'AND'),
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

					// si está activado el plugin Proyectos añadimos el idproyecto del documento
					if (Plugins::isEnabled('Proyectos')) {
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

					if (!$this->getViewModelValue($this->getMainViewName(), 'editable')
					|| $this->getViewModelValue($this->getMainViewName(), 'idfactura')) {
						$this->setSettings($viewName, 'btnDelete', false);
						$this->setSettings($viewName, 'btnNew', false);
						$this->setSettings($viewName, 'checkBoxes', false);
					}

					if ($this->getViewModelValue($this->getMainViewName(), 'idfactura')
					&& $this->getViewModelValue($this->getMainViewName(), 'pagada')) {
						return;
					}

					// Localizamos anticipos sin vincular
					$this->advanceNotLinked($viewName, $subject, $idSubject);

					// Total Pendiente por Liquidar del Documento
					$this->advanceTotalPending($viewName, $modelpc, $codigo);

				default:
					return;
			}
		};
    }

	// Localizamos anticipos sin vincular
	protected function advanceNotLinked($viewName  = 'string', $subject  = 'string', $idSubject  = 'string')
	{
		return function($viewName, $subject, $idSubject) {
			$idempresa = $this->getViewModelValue($this->getMainViewName(), 'idempresa');
			$where = [
				new DataBaseWhere($subject, $idSubject, '='),
				new DataBaseWhere('idempresa', $idempresa, '=', 'AND'),
			];
			if ($viewName === 'ListAnticipoP') {
				foreach(AnticipoP::all($where) as $anticipoSbj) {
					if (false === ($anticipoSbj->idpresupuesto || $anticipoSbj->idpedido || $anticipoSbj->idalbaran || $anticipoSbj->idfactura)) {
						$itemAdv = Tools::trans('advance-not-linked', ['%idAnticipo%' =>$anticipoSbj->id]);
						Tools::log()->warning($itemAdv);
					}
				}
			}
			if ($viewName === 'ListAnticipo') {
				foreach(Anticipo::all($where) as $anticipoSbj) {
					if (false === ($anticipoSbj->idpresupuesto || $anticipoSbj->idpedido || $anticipoSbj->idalbaran || $anticipoSbj->idfactura)) {
						$itemAdv = Tools::trans('advance-not-linked', ['%idAnticipo%' =>$anticipoSbj->id]);
						Tools::log()->warning($itemAdv);
					}
				}
			}
		};
	}

	// Total Pendiente por Liquidar del Documento
	protected function advanceTotalPending($viewName  = 'string', $modelpc  = 'string', $codigo  = 'string')
	{
		return function($viewName, $modelpc, $codigo) {
			$where = [
				new DataBaseWhere($modelpc, $codigo),
			];
			$totalAdvances = 0.00;
			$totalDoc = 0.00;
			$totalPending = 0.00;
			$totalDoc = $this->getViewModelValue($this->getMainViewName(), 'total');
			if ($viewName === 'ListAnticipoP') {
				foreach(AnticipoP::all($where) as $anticipoSbj) {
					$totalAdvances = $totalAdvances +$anticipoSbj->importe;
				}						
			}
			if ($viewName === 'ListAnticipo') {
				foreach(Anticipo::all($where) as $anticipoSbj) {
					$totalAdvances = $totalAdvances +$anticipoSbj->importe;
				}						
			}
			$totalPending = round($this->getViewModelValue($this->getMainViewName(), 'total') - $totalAdvances, 2);
			if ($totalAdvances === 0.00) {
				if (false === (bool)Tools::settings('anticipos', 'msjWa', true)) {
					Tools::Log()->info('without-advances');
				}
			} elseif ($totalAdvances != 0 & $totalPending > 0) {
				Tools::Log()->info('pending-difference-advances', ['%pending%' => Tools::money($totalPending)]);
			} elseif ($totalAdvances != 0 & $totalPending < 0) {
				Tools::Log()->warning('pending-difference-advances', ['%pending%' => Tools::money($totalPending)]);
			} else {
				Tools::Log()->notice('paid');
			}
		};
	}
}