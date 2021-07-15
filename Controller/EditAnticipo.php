<?php
/**
 * This file is part of Anticipos plugin for FacturaScripts
 * Copyright (C) 2020 Carlos Garcia Gomez <carlos@facturascripts.com>
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

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\User;
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
        $data['title'] = 'advance_payment';
        $data['icon'] = 'fas fa-donate';
        return $data;
    }
	 
	/**
     * 
     * @param string   $viewName
     * @param EditView $view
     */
    protected function loadData($viewName, $view)
	{
        switch ($viewName) 
		{
			
			case 'EditAnticipo':
                parent::loadData($viewName, $view);
				
                if(!$this->views[$viewName]->model->exists()) {
                    $this->views[$viewName]->model->user = $this->user->nick;
                }
				
				$fileName = getcwd() . DIRECTORY_SEPARATOR . 'Dinamic'
									 . DIRECTORY_SEPARATOR . 'Controller'
									 . DIRECTORY_SEPARATOR . 'EditProyecto.php';
				
				if (false == $this->user->admin) {
					$this->views[$viewName]->disableColumn('customer', false, 'true');
					$this->views[$viewName]->disableColumn('user', false, 'true');
					$this->views[$viewName]->disableColumn('project', false, 'true');
					$this->views[$viewName]->disableColumn('estimation', false, 'true');
					$this->views[$viewName]->disableColumn('order', false, 'true');
					$this->views[$viewName]->disableColumn('delivery-note', false, 'true');
					$this->views[$viewName]->disableColumn('invoice', false, 'true');
						
				} elseif (false === file_exists ($fileName)) {
					$this->views[$viewName]->disableColumn('project', false, 'true');
				}	
				
                if (!empty ($this->views[$viewName]->model->idfactura)) {
                    $this->views[$viewName]->model->fase = "Factura";

					if (false == $this->user->admin) {
						$this->views[$viewName]->disableColumn('amount', false, 'true');
						$this->views[$viewName]->disableColumn('date', false, 'true');
						$this->views[$viewName]->disableColumn('note', false, 'true');
						$this->views[$viewName]->disableColumn('phase', false, 'true');
						$this->views[$viewName]->disableColumn('payment', false, 'true');
					}
				} elseif (!empty ($this->views[$viewName]->model->idalbaran)) {
                    $this->views[$viewName]->model->fase = "Albaran";
					
                } elseif (!empty ($this->views[$viewName]->model->idpedido)) {
                    $this->views[$viewName]->model->fase = "Pedido";
					
                } elseif (!empty ($this->views[$viewName]->model->idpresupuesto)) {
                    $this->views[$viewName]->model->fase = "Presupuesto";			
					
                } elseif (!empty ($this->views[$viewName]->model->idproyecto)) {
                    $this->views[$viewName]->model->fase = "Proyecto";
									
                } elseif (!empty ($this->views[$viewName]->model->codcliente)) {
                    $this->views[$viewName]->model->fase = "Cliente";

                } elseif (!empty ($this->views[$viewName]->model->user)) {
                    $this->views[$viewName]->model->fase = "Usuario";
                }

				break;
							           
			default:
                parent::loadData($viewName, $view);
                break;
        }
    }
}