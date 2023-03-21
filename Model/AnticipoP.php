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

use FacturaScripts\Dinamic\Model\Proveedor;
use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Model\ReciboProveedor;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\AlbaranProveedor;
use FacturaScripts\Dinamic\Model\FacturaProveedor;
use FacturaScripts\Dinamic\Model\PedidoProveedor;
use FacturaScripts\Dinamic\Model\PresupuestoProveedor;

/**
 * Description of AnticipoP
 *
 * @author Jorge-Prebac <info@prebac.com>
 * @autor Daniel Fernández Giménez <hola@danielfg.es>
 */
class AnticipoP extends Base\ModelClass
{
    use Base\ModelTrait;

    /** @return string */
    public $codproveedor;

    /** @return string */
    public $coddivisa;

    /** @var integer */
    public $id;

    /** @return string */
    public $fase;

	/** @return string */
    public $fecha;

    /** @var integer */
    public $idalbaran;

    /** @var integer */
	public $idempresa;

    /** @var integer */
    public $idfactura;

    /** @var integer */
    public $idpedido;

    /** @var integer */
    public $idpresupuesto;

    /** @var integer */
    public $idproyecto;

    /** @var integer */
    public $idrecibo;

    /** @var float */
    public $importe;

    /** @return string */
    public $nota;

    /** @return string */
    public $user;

    public function __get(string $name)
    {
        switch ($name) {
			case 'riesgomax':
                $proveedor = new Proveedor();
                $proveedor->loadFromCode($this->proveedor);
                return $proveedor->riesgomax;

			case 'totalrisk':
                $proveedor = new Proveedor();
                $proveedor->loadFromCode($this->codproveedor);
                return $proveedor->riesgoalcanzado;

            case 'totaldelivery':
                $delivery = new AlbaranProveedor();
                $delivery->loadFromCode($this->idalbaran);
                return $delivery->total;

            case 'totalestimation':
                $estimation = new PresupuestoProveedor();
                $estimation->loadFromCode($this->idpresupuesto);
                return $estimation->total;

            case 'totalinvoice':
                $invoice = new FacturaProveedor();
                $invoice->loadFromCode($this->idfactura);
                return $invoice->total;

            case 'totalorder':
                $order = new PedidoProveedor();
                $order->loadFromCode($this->idpedido);
                return $order->total;

            case 'totalproject':
                $modelClass = '\\FacturaScripts\\Dinamic\\Model\\Proyecto';
                if (class_exists($modelClass)) {
                    $project = new $modelClass();
                    $project->loadFromCode($this->idproyecto);
                    return $project->totalcompras;
                }
                return 0;
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
        return 'anticiposp';
    }

    public function save(): bool
    {
        // Comprobar que la empresa del anticipo es la misma que la empresa de cada documento
		if (false === $this->checkCompanies() ) {
            return false;
        }

	// Comprobar que el Proveedor del anticipo es el mismo que el Proveedor de cada documento
        if (false === $this->checkProveedores() ) {
            return false;
        }

        // si el anticipo tiene una factura asignada, entonces creamos tantos recibos como anticipos tenga
        /*if ($this->idfactura && false === $this->generateReceiptsInvoice()) {
            return false;
        }*/

        return parent::save();
    }
	
	protected function checkCompanies(): bool
	{
		if ($this->idempresa && $this->idpresupuesto) {
			$estimation = new PresupuestoProveedor();
            $estimation->loadFromCode($this->idpresupuesto);
            if ($estimation->idempresa != $this->idempresa) {
				$this->toolBox()->i18nLog()->warning('advance-payment-invalid-company-estimation');
				return false;
            }
        }elseif(!$this->idempresa && $this->idpresupuesto) {
           $this->toolBox()->i18nLog()->warning('missing-company-name');
            return false;
		}
		
		if ($this->idempresa && $this->idpedido) {
			$order = new PedidoProveedor();
            $order->loadFromCode($this->idpedido);
            if ($order->idempresa != $this->idempresa) {
				$this->toolBox()->i18nLog()->warning('advance-payment-invalid-company-order');
				return false;
            }
        }elseif(!$this->idempresa && $this->idpedido) {
           $this->toolBox()->i18nLog()->warning('missing-company-name');
            return false;
		}
		
		if ($this->idempresa && $this->idalbaran) {
			$deliveryNote = new AlbaranProveedor();
            $deliveryNote->loadFromCode($this->idalbaran);
            if ($deliveryNote->idempresa != $this->idempresa) {
				$this->toolBox()->i18nLog()->warning('advance-payment-invalid-company-deliveryNote');
				return false;
            }
        }elseif(!$this->idempresa && $this->idalbaran) {
           $this->toolBox()->i18nLog()->warning('missing-company-name');
            return false;
		}
		
		if ($this->idempresa && $this->idfactura) {
			$invoice = new FacturaProveedor();
            $invoice->loadFromCode($this->idfactura);
            if ($invoice->idempresa != $this->idempresa) {
				$this->toolBox()->i18nLog()->warning('advance-payment-invalid-company-invoice');
				return false;
            }
        }elseif(!$this->idempresa && $this->idfactura) {
           $this->toolBox()->i18nLog()->warning('missing-company-name');
            return false;
		}
		
        $projectClass = '\\FacturaScripts\\Dinamic\\Model\\Proyecto';
        if ($this->idempresa && $this->idproyecto && class_exists($projectClass)) {
            $project = new $projectClass();
            $project->loadFromCode($this->idproyecto);
            if ($project->idempresa && $project->idempresa != $this->idempresa) {
                $this->toolBox()->i18nLog()->warning('advance-payment-invalid-company-project');
                return false;
            }
        }elseif(!$this->idempresa && $this->idproyecto && class_exists($projectClass)) {
            $project = new $projectClass();
            $project->loadFromCode($this->idproyecto);
            if ($project->idempresa && $project->idempresa != $this->idempresa) {
                $this->toolBox()->i18nLog()->warning('missing-company-name');
                return false;
			}
		}

		return true;
	}

    protected function checkProveedores(): bool
    {
        if ($this->codproveedor && $this->idpresupuesto) {
            $estimation = new PresupuestoProveedor();
            $estimation->loadFromCode($this->idpresupuesto);
            if ($estimation->codproveedor != $this->codproveedor) {
                $this->toolBox()->i18nLog()->warning('advance-payment-invalid-supplier-estimation');
                return false;
            }
        }elseif(!$this->codproveedor && $this->idpresupuesto) {
			$this->toolBox()->i18nLog()->warning('missing-supplier-name');
            return false;
		}

        if ($this->codproveedor && $this->idpedido) {
            $order = new PedidoProveedor();
            $order->loadFromCode($this->idpedido);
            if ($order->codproveedor != $this->codproveedor) {
                $this->toolBox()->i18nLog()->warning('advance-payment-invalid-supplier-order');
				return false;
			}
		}elseif(!$this->codproveedor && $this->idpedido) {
			$this->toolBox()->i18nLog()->warning('missing-supplier-name');
            return false;
		}

        if ($this->codproveedor && $this->idalbaran) {
            $deliveryNote = new AlbaranProveedor();
            $deliveryNote->loadFromCode($this->idalbaran);
            if ($deliveryNote->codproveedor != $this->codproveedor) {
                $this->toolBox()->i18nLog()->warning('advance-payment-invalid-supplier-delivery-note');
                return false;
            }
        }elseif(!$this->codproveedor && $this->idalbaran) {
			$this->toolBox()->i18nLog()->warning('missing-supplier-name');
            return false;
		}

        if ($this->codproveedor && $this->idfactura) {
            $invoice = new FacturaProveedor();
            $invoice->loadFromCode($this->idfactura);
            if ($invoice->codproveedor != $this->codproveedor) {
                $this->toolBox()->i18nLog()->warning('advance-payment-invalid-supplier-invoice');
                return false;
            }
        }elseif(!$this->codproveedor && $this->idfactura) {
			$this->toolBox()->i18nLog()->warning('missing-supplier-name');
            return false;
		}

        return true;
    }

    protected function generateReceiptsInvoice(): bool
    {
        // obtenemos todos los recibos que tenga ya la factura
        $oldReciboModel = new ReciboProveedor();
        $where = [new DataBaseWhere('idfactura', $this->idfactura)];
        $oldRecibos = $oldReciboModel->all($where, [], 0, 0);

        // este es el recibo que se crea por defecto al crear la factura
        $oldRecibo = new ReciboProveedor();
        $oldRecibo->loadFromCode('', $where);

        // comprobamos si el resto del total del recibo viejo menos el total del anticipo es 0
		// si el resto es igual a 0 modificamos el recibo viejo con los datos del anticipo
        $resto = $oldRecibo->importe - $this->importe;
		if ($resto == 0) {

            // marcamos el recibo viejo como pagado
            $oldRecibo->pagado = 1;
			
			// actualizamos las columnas del recibo viejo con las del anticipo
			$oldRecibo->codpago = $this->codpago;
			$oldRecibo->vencimiento = $this->fecha;
			
			if ($this->toolBox()->appSettings()->get('anticipos', 'pdAnticipos', false)) {
				$oldRecibo->fecha = $this->fecha;
				$oldRecibo->fechapago = $this->fecha;
			}

			$oldRecibo->observaciones = $this->nota;
            $oldRecibo->save();

            return true;
        }
		
		// si el resto es distinto a 0 creamos nuevos recibos con base a los anticipos
		if ($resto !== 0) {

			// creamos los nuevos recibos
			$newRecibo = new ReciboProveedor();
			$newRecibo->codproveedor = $oldRecibos[0]->codproveedor;
			$newRecibo->coddivisa = $this->coddivisa;
			$newRecibo->codigofactura = $oldRecibos[0]->codigofactura;
			$newRecibo->codpago = $this->codpago;
			$newRecibo->fecha = $this->fecha;
			$newRecibo->vencimiento = $this->fecha;
			if ($this->toolBox()->appSettings()->get('anticipos', 'pdAnticipos', false)) {
				$newRecibo->fechapago = $this->fecha;
			}
			$newRecibo->idempresa = $oldRecibos[0]->idempresa;
			$newRecibo->idfactura = $this->idfactura;
			$newRecibo->importe = $this->importe;
			$newRecibo->nick = $oldRecibos[0]->nick;
			$newRecibo->numero = count($oldRecibos);
			$newRecibo->observaciones = $this->nota;
			$newRecibo->pagado = 1;

			if (false === $newRecibo->save()) {
				return false;
			}

			// actualizamos el recibo viejo con la diferencia del recibo viejo menos el importe del anticipo actual
			$oldRecibo->importe = $resto;
			$oldRecibo->numero = count($oldRecibos) + 1;
			return $oldRecibo->save();
		}
    }
}
