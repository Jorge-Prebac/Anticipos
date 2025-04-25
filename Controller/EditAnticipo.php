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
use FacturaScripts\Core\Model\AlbaranCliente;


/**
 * Description of EditAnticipo
 *
 * @author Jorge-Prebac <info@prebac.com>
 */
class EditAnticipo extends EditController
{

    /**
     *
     * @return string
     */
    public function getModelClassName(): string
    {
        return 'Anticipo';
    }

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'sales';
        $data['title'] = 'advance-payment-c';
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
    
            case 'EditAnticipo':
                parent::loadData($viewName, $view);
                $model = $this->views[$viewName]->model;
    
                // solo si es un anticipo nuevo
                if (false === $model->exists()) {
                    $model->nick = $this->user->nick;
    
                    // si viene de un albarán, asignar importe
                    if (!empty($model->idalbaran)) {
                        $delivery = new AlbaranCliente();
                        $delivery->loadFromCode($model->idalbaran);
                        $model->importe = $delivery->total;
                    }
    
                    // asignar fase automáticamente según el origen
                    if (!empty($model->idalbaran)) {
                        $model->fase = "Albaran";
                    } elseif (!empty($model->idpedido)) {
                        $model->fase = "Pedido";
                    } elseif (!empty($model->idpresupuesto)) {
                        $model->fase = "Presupuesto";
                    } elseif (!empty($model->idproyecto)) {
                        $model->fase = "Proyecto";
                    } elseif (!empty($model->codcliente)) {
                        $model->fase = "Cliente";
                    } elseif (!empty($model->user)) {
                        $model->fase = "Usuario";
                    }
                }
    
                // valores para el select de la fase
                $customValues = [
                    ['value' => 'Albaran', 'title' => 'delivery-note'],
                    ['value' => 'Cliente', 'title' => 'customer'],
                    ['value' => 'Pedido', 'title' => 'order'],
                    ['value' => 'Presupuesto', 'title' => 'estimation'],
                    ['value' => 'Usuario', 'title' => 'user'],
                ];
    
                // si está instalado el plugin Proyectos añadimos la opción
                if (class_exists('\\FacturaScripts\\Dinamic\\Model\\Proyecto')) {
                    $customValues[] = ['value' => 'Proyecto', 'title' => 'project'];
                }
    
                // rellenar el select de la fase
                $column = $this->views[$viewName]->columnForName('phase');
                if ($column && $column->widget->getType() === 'select') {
                    $column->widget->setValuesFromArray($customValues, true);
                }
    
                // si no hay plugin Proyectos, ocultar columnas relacionadas
                if (!class_exists('\\FacturaScripts\\Dinamic\\Model\\Proyecto')) {
                    $this->views[$viewName]->disableColumn('project');
                    $this->views[$viewName]->disableColumn('project-total-amount');
                }
    
                // no se puede editar el campo idfactura
                $this->views[$viewName]->disableColumn('invoice', false, 'true');
    
                // si el anticipo está vinculado a una factura, desactivar edición de ciertos campos
                if (!empty($model->idfactura)) {
                    $this->views[$viewName]->disableColumn('amount', false, 'true');
                    $this->views[$viewName]->disableColumn('date', false, 'true');
                    $this->views[$viewName]->disableColumn('note', false, 'true');
                    $this->views[$viewName]->disableColumn('payment', false, 'true');
                    $this->views[$viewName]->disableColumn('phase', false, 'true');
                    $this->setSettings($viewName, 'btnDelete', false);
                }
    
                // control por nivel de seguridad
                if (empty(Tools::settings('anticipos', 'level'))) {
                    Tools::Log()->warning('level-not-configured');
                    $this->views[$viewName]->setReadOnly(true);
                } elseif (!empty($model->importe) && ($this->user->level < Tools::settings('anticipos', 'level'))) {
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