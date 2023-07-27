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
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Model\ReciboCliente;
use FacturaScripts\Dinamic\Model\AlbaranCliente;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\FacturaCliente;
use FacturaScripts\Dinamic\Model\PedidoCliente;
use FacturaScripts\Dinamic\Model\PresupuestoCliente;

/**
 * Description of Anticipo
 *
 * @autor Jorge-Prebac <info@prebac.com>
 * @autor Daniel Fernández Giménez <hola@danielfg.es>
 */
class Anticipo extends Base\ModelClass
{
    use Base\ModelTrait;

    /** @return string */
    public $codcliente;

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
                $cliente = new Cliente();
                $cliente->loadFromCode($this->codcliente);
                return $cliente->riesgomax;

			case 'totalrisk':
                $cliente = new Cliente();
                $cliente->loadFromCode($this->codcliente);
                return $cliente->riesgoalcanzado;

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

            case 'totalorder':
                $order = new PedidoCliente();
                $order->loadFromCode($this->idpedido);
                return $order->total;

            case 'totalproject':
                $modelClass = '\\FacturaScripts\\Dinamic\\Model\\Proyecto';
                if (class_exists($modelClass)) {
                    $project = new $modelClass();
                    $project->loadFromCode($this->idproyecto);
                    return $project->totalventas;
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
        return 'anticipos';
    }

    public function save(): bool
    {
        // Comprobar que la empresa del anticipo es la misma que la empresa de cada documento
        if (false === $this->checkCompanies()) {
            return false;
        }

        // Comprobar que el Cliente del anticipo es el mismo que el Cliente de cada documento
        if (false === $this->checkClients()) {
            return false;
        }

		// add audit log
		self::toolBox()::i18nLog(self::AUDIT_CHANNEL)->info('updated-model', [
			'%model%' => $this->modelClassName(),
			'%key%' => $this->primaryColumnValue(),
			'%desc%' => $this->primaryDescription(),
			'model-class' => $this->modelClassName(),
			'model-code' => $this->primaryColumnValue(),
			'model-data' => $this->toArray()
		]);

		return parent::save();
    }

	public function delete(): bool
    {
        if (false === parent::delete()) {
            return false;
        }

		// add audit log
		self::toolBox()::i18nLog(self::AUDIT_CHANNEL)->info('deleted-model', [
			'%model%' => $this->modelClassName(),
			'%key%' => $this->primaryColumnValue(),
			'%desc%' => $this->primaryDescription(),
			'model-class' => $this->modelClassName(),
			'model-code' => $this->primaryColumnValue(),
			'model-data' => $this->toArray()
		]);

		return true;
    }

	protected function checkCompanies(): bool
	{
		if ($this->idempresa && $this->idpresupuesto) {
			$estimation = new PresupuestoCliente();
            $estimation->loadFromCode($this->idpresupuesto);
            if ($estimation->idempresa != $this->idempresa) {
				$this->toolBox()->i18nLog()->warning('advance-payment-invalid-company-estimation');
				return false;
            }
        } elseif (!$this->idempresa && $this->idpresupuesto) {
			$this->toolBox()->i18nLog()->warning('missing-company-name');
            return false;
		}

		if ($this->idempresa && $this->idpedido) {
			$order = new PedidoCliente();
            $order->loadFromCode($this->idpedido);
            if ($order->idempresa != $this->idempresa) {
				$this->toolBox()->i18nLog()->warning('advance-payment-invalid-company-order');
				return false;
            }
        } elseif (!$this->idempresa && $this->idpedido) {
			$this->toolBox()->i18nLog()->warning('missing-company-name');
            return false;
		}

		if ($this->idempresa && $this->idalbaran) {
			$deliveryNote = new AlbaranCliente();
            $deliveryNote->loadFromCode($this->idalbaran);
            if ($deliveryNote->idempresa != $this->idempresa) {
				$this->toolBox()->i18nLog()->warning('advance-payment-invalid-company-deliveryNote');
				return false;
            }
        } elseif (!$this->idempresa && $this->idalbaran) {
			$this->toolBox()->i18nLog()->warning('missing-company-name');
            return false;
		}

		if ($this->idempresa && $this->idfactura) {
			$invoice = new FacturaCliente();
            $invoice->loadFromCode($this->idfactura);
            if ($invoice->idempresa != $this->idempresa) {
				$this->toolBox()->i18nLog()->warning('advance-payment-invalid-company-invoice');
				return false;
            }
        } elseif (!$this->idempresa && $this->idfactura) {
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
        } elseif (!$this->idempresa && $this->idproyecto && class_exists($projectClass)) {
			$this->toolBox()->i18nLog()->warning('missing-company-name');
			return false;
		}

		return true;
	}

    protected function checkClients(): bool
    {
        if ($this->codcliente && $this->idpresupuesto) {
            $estimation = new PresupuestoCliente();
            $estimation->loadFromCode($this->idpresupuesto);
            if ($estimation->codcliente != $this->codcliente) {
                $this->toolBox()->i18nLog()->warning('advance-payment-invalid-client-estimation');
                return false;
            }
        } elseif (!$this->codcliente && $this->idpresupuesto) {
			$this->toolBox()->i18nLog()->warning('missing-customer-name');
            return false;
		}

        if ($this->codcliente && $this->idpedido) {
            $order = new PedidoCliente();
            $order->loadFromCode($this->idpedido);
            if ($order->codcliente != $this->codcliente) {
                $this->toolBox()->i18nLog()->warning('advance-payment-invalid-client-order');
				return false;
			}
		} elseif (!$this->codcliente && $this->idpedido) {
			$this->toolBox()->i18nLog()->warning('missing-customer-name');
            return false;
		}

        if ($this->codcliente && $this->idalbaran) {
            $deliveryNote = new AlbaranCliente();
            $deliveryNote->loadFromCode($this->idalbaran);
            if ($deliveryNote->codcliente != $this->codcliente) {
                $this->toolBox()->i18nLog()->warning('advance-payment-invalid-client-delivery-note');
                return false;
            }
        } elseif (!$this->codcliente && $this->idalbaran) {
			$this->toolBox()->i18nLog()->warning('missing-customer-name');
            return false;
		}

        if ($this->codcliente && $this->idfactura) {
            $invoice = new FacturaCliente();
            $invoice->loadFromCode($this->idfactura);
            if ($invoice->codcliente != $this->codcliente) {
                $this->toolBox()->i18nLog()->warning('advance-payment-invalid-client-invoice');
                return false;
            }
        } elseif (!$this->codcliente && $this->idfactura) {
			$this->toolBox()->i18nLog()->warning('missing-customer-name');
            return false;
		}

        $projectClass = '\\FacturaScripts\\Dinamic\\Model\\Proyecto';
        if ($this->codcliente && $this->idproyecto && class_exists($projectClass)) {
            $project = new $projectClass();
            $project->loadFromCode($this->idproyecto);
            if ($project->codcliente && $project->codcliente != $this->codcliente) {
                $this->toolBox()->i18nLog()->warning('advance-payment-invalid-client-project');
                return false;
            }
        } elseif (!$this->codcliente && $this->idproyecto && class_exists($projectClass)) {
            $project = new $projectClass();
            $project->loadFromCode($this->idproyecto);
            if ($project->codcliente) {
                $this->toolBox()->i18nLog()->warning('missing-customer-name');
                return false;
            }
        }

        return true;
    }
}