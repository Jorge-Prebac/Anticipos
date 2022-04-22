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

namespace FacturaScripts\Plugins\Anticipos\Extension\Model;

use Closure;
use FacturaScripts\Plugins\Anticipos\Model\Anticipo;
use FacturaScripts\Core\Model\EstadoDocumento;
use FacturaScripts\Core\Model\DocTransformation;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of PresupuestoCliente
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class PresupuestoCliente
{
    public function saveUpdate(): Closure
    {
        return function () {
            $estado = new EstadoDocumento();
            $estado->loadFromCode($this->idestado);

            if ($estado->generadoc) {
                $anticiposModel = new Anticipo();
                $anticipos = $anticiposModel->all([
                    new DataBaseWhere('idpresupuesto', $this->idpresupuesto)
                ]);

                if (count($anticipos) > 0) {
                    $newDoc = new DocTransformation();
                    $where = [
                        new DataBaseWhere('model1', 'PresupuestoCliente'),
                        new DataBaseWhere('iddoc1', $this->idpresupuesto)
                    ];
                    $newDoc->loadFromCode('', $where);

                    foreach ($anticipos as $a) {
                        $anticipo = new Anticipo();
                        $anticipo->loadFromCode($a->id);

                        switch ($newDoc->model2) {
                            case 'PresupuestoCliente':
                                $anticipo->idpresupuesto = $newDoc->iddoc2;
                                break;
                            case 'PedidoCliente':
                                $anticipo->idpedido = $newDoc->iddoc2;
                                break;
                            case 'AlbaranCliente':
                                $anticipo->idalbaran = $newDoc->iddoc2;
                                break;
                            case 'FacturaCliente':
                                $anticipo->idfactura = $newDoc->iddoc2;
                                break;
                        }

                        $anticipo->save();
                    }
                }
            }
        };
    }
}