<?php
/**
 * This file is part of the AdmReportico plugin, with the Reportico engine, for FacturaScripts
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

use Closure;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of ListAlbaranCliente
 *
 * @author Jorge-Prebac <info@prebac.com>
 */
 
class ListAlbaranCliente
{
	public function createViews(): Closure
	{
		return function() {
			$viewName = 'ListAlbaranCliente';
			
			$i18n = $this->toolBox()->i18n();
			$this->addFilterSelectWhere($viewName, 'status', [
				['label' => $i18n->trans('advance-payments'), 'where' => []],
				['label' => $i18n->trans('with-advances'), 'where' => [new DataBaseWhere('advance', 0, '>')]],
				['label' => $i18n->trans('without-advances'), 'where' => [new DataBaseWhere('advance', 0, '=')]],
			]);
		};
	}
}
