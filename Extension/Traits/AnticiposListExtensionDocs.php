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
 
namespace FacturaScripts\Plugins\Anticipos\Extension\Traits;

use Closure;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of AnticiposListExtensionDocs
 *
 * @author Jorge-Prebac <info@prebac.com>
 */
 
trait AnticiposListExtensionDocs
{
	public function createViews(): Closure
	{
		return function() {
			$viewName = $this->getMainViewName();
			$this->addFilterSelectWhere($viewName, 'advances-status', [
				['label' => Tools::lang()->trans('advances-status'), 'where' => []],
				['label' => Tools::lang()->trans('with-advances'), 'where' => [new DataBaseWhere('advance', 0, '>')]],
				['label' => Tools::lang()->trans('without-advances'), 'where' => [
							new DataBaseWhere('advance', 0),
							new DataBaseWhere('advance', null, '=', 'OR'),
					]],
			]);
		};
	}
}
