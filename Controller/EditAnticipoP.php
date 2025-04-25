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

namespace FacturaScripts\Plugins\Anticipos\Controller;

use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\BaseView;
use FacturaScripts\Core\Lib\ExtendedController\EditController;
use FacturaScripts\Core\Model\AlbaranProveedor;


/**
 * Description of EditAnticipoP
 *
 * @author Jorge-Prebac <info@prebac.com>
 */
class EditAnticipoP extends EditController
{

    /**
     *
     * @return string
     */
    public function getModelClassName(): string
    {
        return 'AnticipoP';
    }

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'purchases';
        $data['title'] = 'advance-payment-p';
        $data['icon'] = 'fas fa-donate';
		$data['showonmenu'] = false;
        return $data;
    }

    protected function createViews()
    {
        parent::createViews();
        $this->createViewLogs();
		$this->setTabsPosition('top');
    }

    public function createViewLogs(string $viewName = 'ListLogMessage'): void
    {
        $this->addListView($viewName, 'LogMessage', 'history', 'fas fa-history')
			->addOrderBy(['time', 'id'], 'date', 2)
			->addSearchFields(['context', 'message']);

        // disable buttons
		$this->tab($viewName)
			->setSettings('btnDelete', false)
			->setSettings('btnNew', false)
			->setSettings('checkBoxes', false);
    }

    /**
     *
     * @param string $viewName
     * @param BaseView $view
     */
    protected function loadData($viewName, $view)
    {
        switch ($viewName) {

            case 'EditAnticipoP':
                parent::loadData($viewName, $view);
                $model = $this->views[$viewName]->model;

                // si es un anticipo nuevo, se le asigna el usuario que lo creó
                if (false === $model->exists()) {
					$model->nick = $this->user->nick;

                    // si viene de un albarán, asignar importe
                    if (!empty($model->idalbaran)) {
                        $delivery = new AlbaranProveedor();
                        $delivery->loadFromCode($model->idalbaran);
                        $model->importe = $delivery->total;
                    }
                    // se aplica la fase correspondiente al origen del anticipo
                    if (false === empty($model->idalbaran)) {
                        $model->fase = "Albaran";
                    } elseif (false === empty($model->idpedido)) {
                        $model->fase = "Pedido";
                    } elseif (false === empty($model->idpresupuesto)) {
                        $model->fase = "Presupuesto";
                    } elseif (false === empty($model->idproyecto)) {
                        $model->fase = "Proyecto";
                    } elseif (false === empty($model->codproveedor)) {
                        $model->fase = "Proveedor";
                    } elseif (false === empty($model->user)) {
                        $model->fase = "Usuario";
                    }
                }

                // valores para el select de la fase
                $customValues = [
					['value' => 'Albaran', 'title' => 'delivery-note'],
                    ['value' => 'Proveedor', 'title' => 'supplier'],
					['value' => 'Pedido', 'title' => 'order'],
                    ['value' => 'Presupuesto', 'title' => 'estimation'],
					['value' => 'Usuario', 'title' => 'user'],
                ];
				
				// si está instalado el plugin Proyectos añadimos el valor para el select de la fase
				if (true === class_exists('\\FacturaScripts\\Dinamic\\Model\\Proyecto')) {
					$customValues[] = ['value' => 'Proyecto', 'title' => 'project'];
				}

				// rellenamos el select de la fase
                $column = $this->views[$viewName]->columnForName('phase');
                if ($column && $column->widget->getType() === 'select') {
                    $column->widget->setValuesFromArray($customValues, true);
                }

                // si no está instalado el plugin Proyectos ocultamos sus columnas
                if (false === class_exists('\\FacturaScripts\\Dinamic\\Model\\Proyecto')) {
                    $this->views[$viewName]->disableColumn('project');
                    $this->views[$viewName]->disableColumn('project-total-amount');
				}

				// no se puede editar el campo idfactura
				$this->views[$viewName]->disableColumn('invoice', false, 'true');

                // si el anticipo es de una factura no se pueden editar las siguientes columnas
                if (false === empty($model->idfactura)) {
                    $this->views[$viewName]->disableColumn('amount', false, 'true');
                    $this->views[$viewName]->disableColumn('date', false, 'true');
                    $this->views[$viewName]->disableColumn('note', false, 'true');
                    $this->views[$viewName]->disableColumn('payment', false, 'true');
					$this->views[$viewName]->disableColumn('phase', false, 'true');
					$this->setSettings($viewName, 'btnDelete', false);
                }

				// mensaje si no está configurado el nivel mínimo para que un usuario pueda modificar anticipos 
				// mensaje si el usuario tiene un nivel de seguridad menor del configurado, no podrá modificar los datos de los anticipos
				if (empty(Tools::settings('anticipos', 'level'))) {
					Tools::Log()->warning('level-not-configured');
					$this->views[$viewName]->setReadOnly(true);
				}elseif (false === empty($model->importe) && ($this->user->level < (Tools::settings('anticipos', 'level')))) {
					Tools::Log()->warning('not-allowed-modify');
					$this->views[$viewName]->setReadOnly(true);
				}

                break;

			case 'ListLogMessage':
				parent::loadData($viewName, $view);
				$where = [
					new DataBaseWhere('model', $this->getModelClassName()),
					new DataBaseWhere('modelcode', $this->getModel()->primaryColumnValue())
				];
				$view->loadData('', $where);
				break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }
}
