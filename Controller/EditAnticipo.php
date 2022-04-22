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

namespace FacturaScripts\Plugins\Anticipos\Controller;

use FacturaScripts\Core\Lib\ExtendedController\BaseView;
use FacturaScripts\Core\Lib\ExtendedController\EditController;

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
    public function getModelClassName()
    {
        return 'Anticipo';
    }

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'sales';
        $data['title'] = 'advance-payment';
        $data['icon'] = 'fas fa-donate';
        return $data;
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
                
                // si es un anticipo nuevo, se le asigna el usuario que lo creó
                if (false === $model->exists()) {
                    $model->user = $this->user->nick;
                }

                // valores para el select de la fase
                $customValues = [
                    ['value' => 'Usuario', 'title' => 'user'],
                    ['value' => 'Cliente', 'title' => 'customer'],
                    ['value' => 'Proyecto', 'title' => 'project'],
                    ['value' => 'Presupuesto', 'title' => 'estimation'],
                    ['value' => 'Pedido', 'title' => 'order'],
                    ['value' => 'Albaran', 'title' => 'delivery-note'],
                    ['value' => 'Factura', 'title' => 'invoice'],
                ];

                // si no está instalado el plugin Proyectos ocultamos sus columnas
                if (false === class_exists('\\FacturaScripts\\Dinamic\\Model\\Proyecto')) {
                    $this->views[$viewName]->disableColumn('project');
                    $this->views[$viewName]->disableColumn('project-total-amount');
                } else {
                    $customValues[] = ['value' => 'Proyecto', 'title' => 'project'];
                }

                // rellenamos el select de la fase
                $column = $this->views[$viewName]->columnForName('phase');
                if($column && $column->widget->getType() === 'select') {
                    $column->widget->setValuesFromArray($customValues, true);
                }

                // si no eres admin, no puedes editar algunas columnas
                if (false === $this->user->admin) {
                    $this->views[$viewName]->disableColumn('customer', false, 'true');
                    $this->views[$viewName]->disableColumn('user', false, 'true');
                    $this->views[$viewName]->disableColumn('project', false, 'true');
                    $this->views[$viewName]->disableColumn('estimation', false, 'true');
                    $this->views[$viewName]->disableColumn('order', false, 'true');
                    $this->views[$viewName]->disableColumn('delivery-note', false, 'true');
                    $this->views[$viewName]->disableColumn('invoice', false, 'true');
                }

                // si el anticipo es de una facutra y no eres admin no puedes editar estos campos
                if (false === empty($model->idfactura) && false === $this->user->admin) {
                    $this->views[$viewName]->disableColumn('amount', false, 'true');
                    $this->views[$viewName]->disableColumn('date', false, 'true');
                    $this->views[$viewName]->disableColumn('note', false, 'true');
                    $this->views[$viewName]->disableColumn('phase', false, 'true');
                    $this->views[$viewName]->disableColumn('payment', false, 'true');
                }

                if (false === empty($model->idfactura) && false === $model->exists()) {
                    $model->fase = "Factura";
                } elseif (false === empty($model->idalbaran) && false === $model->exists()) {
                    $model->fase = "Albaran";
                } elseif (false === empty($model->idpedido) && false === $model->exists()) {
                    $model->fase = "Pedido";
                } elseif (false === empty($model->idpresupuesto) && false === $model->exists()) {
                    $model->fase = "Presupuesto";
                } elseif (false === empty($model->idproyecto) && false === $model->exists()) {
                    $model->fase = "Proyecto";
                } elseif (false === empty($model->codcliente) && false === $model->exists()) {
                    $model->fase = "Cliente";
                } elseif (false === empty($model->user) && false === $model->exists()) {
                    $model->fase = "Usuario";
                }

                break;

            default:
                parent::loadData($viewName, $view);
                break;
        }
    }
}