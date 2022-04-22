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

namespace FacturaScripts\Plugins\Anticipos\Model;

use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Model\ReciboCliente;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\AlbaranCliente;
use FacturaScripts\Dinamic\Model\FacturaCliente;
use FacturaScripts\Dinamic\Model\PedidoCliente;
use FacturaScripts\Dinamic\Model\PresupuestoCliente;

/**
 * Description of Anticipo
 *
 * @author Jorge-Prebac <info@prebac.com>
 * @autor Daniel Fernández Giménez <hola@danielfg.es>
 */
class Anticipo extends Base\ModelClass
{
    use Base\ModelTrait;

    /**
     *
     * @return string
     */
    public $codcliente;

    /**
     *
     * @return string
     */
    public $coddivisa;

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @return string
     */
    public $fase;

    /**
     *
     * @var string
     */
    public $fecha;

    /**
     *
     * @var integer
     */
    public $idalbaran;

    /**
     *
     * @var integer
     */
    public $idfactura;

    /**
     *
     * @var integer
     */
    public $idpedido;

    /**
     *
     * @var integer
     */
    public $idpresupuesto;

    /**
     *
     * @var integer
     */
    public $idproyecto;

    /**
     *
     * @var integer
     */
    public $idrecibo;

    /**
     *
     * @var float
     */
    public $importe;

    /**
     *
     * @return string
     */
    public $nota;

    /**
     *
     * @return string
     */
    public $user;

    public function __get(string $name)
    {
        switch ($name) {
            case 'totaldelivery':
                $delivery = new AlbaranCliente();
                $delivery->loadFromCode($this->idalbaran);
                return $delivery->total;

            case 'totalestimation':
                $estimation = new PresupuestoCliente();
                $estimation->loadFromCode($this->idpresupuesto);
                return $estimation->total;

            case 'totalinvoice':
                $invoice = new FacturaCliente();
                $invoice->loadFromCode($this->idfactura);
                return $invoice->total;

            case 'totalproject':
                $modelClass = '\\FacturaScripts\\Dinamic\\Model\\Proyecto';
                if (class_exists($modelClass)) {
                    $project = new $modelClass();
                    $project->loadFromCode($this->idalbaran);
                    return $project->totalventas;
                }
                return 0;

            case 'totalorder':
                $order = new PedidoCliente();
                $order->loadFromCode($this->idpedido);
                return $order->total;
        }
        return null;
    }

    public function clear()
    {
        parent::clear();
        $this->coddivisa = AppSettings::get('default', 'coddivisa');
        $this->fecha = \date(self::DATE_STYLE);
        $this->importe = 0;
    }

    /**
     *
     * @return string
     */
    public static function primaryColumn(): string
    {
        return 'id';
    }

    /**
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'anticipos';
    }

    public function save()
    {
        if ($this->idfactura) {
            $where = [new DataBaseWhere('idfactura', $this->idfactura)];
            $oldReciboModel = new ReciboCliente();
            $oldRecibos = $oldReciboModel->all($where);

            $oldRecibo = new ReciboCliente();
            $oldRecibo->loadFromCode('', $where);

            $anticipo = new Anticipo();
            $anticipo->loadFromCode($this->id);

            $newRecibo = new ReciboCliente();
            $newRecibo->codcliente = $oldRecibos[0]->codcliente;
            $newRecibo->coddivisa = $anticipo->coddivisa;
            $newRecibo->codigofactura = $oldRecibos[0]->codigofactura;
            $newRecibo->codpago = $anticipo->codpago;
            $newRecibo->fecha = $anticipo->fecha;
            if (true == $this->toolBox()->appSettings()->get('anticipos', 'pdAnticipos')) {
                $newRecibo->fechapago = $anticipo->fecha;
            }
            $newRecibo->idempresa = $oldRecibos[0]->idempresa;
            $newRecibo->idfactura = $this->idfactura;
            $newRecibo->importe = $anticipo->importe;
            $newRecibo->nick = $oldRecibos[0]->nick;
            $newRecibo->numero = count($oldRecibos) + 1;
            $newRecibo->observaciones = $anticipo->nota;
            $newRecibo->pagado = 1;
            $newRecibo->save();

            $oldRecibo->importe = $oldRecibo->importe - $anticipo->importe;
            $oldRecibo->save();
        }

        return parent::save();
    }
}