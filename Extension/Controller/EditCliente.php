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
namespace FacturaScripts\Plugins\Anticipos\Extension\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of EditCliente
 *
 * @author Jorge-Prebac <info@prebac.com>
 */

class EditCliente
{
    public function createViews()
    {
        return function()
		{
			$viewName = 'ListAnticipo';
			$this->addListView($viewName,'Anticipo','advance_payments','fas fa-donate');
			$this->views[$viewName]->addOrderBy(['fecha'], 'date', 2); 
			$this->views[$viewName]->addOrderBy(['fase'], 'phase');
			$this->views[$viewName]->addOrderBy(['importe'], 'amount');
		};
    }

    public function loadData()
	{
        return function($viewName, $view)
		{
            if ($viewName === 'ListAnticipo')
			{
				$codcliente = $this->getViewModelValue($this->getMainViewName(), 'codcliente');
                $where = [new DataBaseWhere('codcliente', $codcliente)];
                $view->loadData('', $where);
			}
		};
    }
}
