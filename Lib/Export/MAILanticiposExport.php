<?php

namespace FacturaScripts\Plugins\Anticipos\Lib\Export;

class MAILanticiposExport extends \FacturaScripts\Core\Lib\Export\MAILExport
{
    public function addModelPage($model, $columns, $title = ''): bool
    {
        $this->newPage();
        $idempresa = $model->idempresa ?? null;
        $this->insertHeader($idempresa);

        $tableCols = [];
        $tableColsTitle = [];
        $tableOptions = [
            'width' => $this->tableWidth,
            'showHeadings' => 0,
            'shaded' => 0,
            'lineCol' => [1, 1, 1],
            'cols' => []
        ];

        // Get the columns
        $this->setTableColumns($columns, $tableCols, $tableColsTitle, $tableOptions);

        $tableDataAux = [];

		foreach ($tableCols as $key => $colName) {
			$value = $tableOptions['cols'][$key]['widget']->plainText($model);
			
			if (false !== strpos($colName, 'total')) {
				continue;
			}elseif (false !== strpos($colName, 'user')) {
				continue;
			}elseif (false !== strpos($colName, 'importe')) {
				$colName = $this->i18n->trans('amount');
			}elseif (false !== strpos($colName, 'nota')) {
				$colName = $this->i18n->trans('note');
			}elseif (false !== strpos($colName, 'codpago')) {
				$colName = $this->i18n->trans('method-payment');
			}elseif (false !== strpos($colName, 'fecha')) {
				$colName = $this->i18n->trans('date');
			}elseif (false !== strpos($colName, 'fase')) {
				$colName = $this->i18n->trans('advance-linked-to');
			}elseif (false !== strpos($colName, 'codcliente')) {
				$colName = $this->i18n->trans('customer');
			}elseif (false !== strpos($colName, 'idpresupuesto')) {
				$colName = $this->i18n->trans('estimation');
			}elseif (false !== strpos($colName, 'idpedido')) {
				$colName = $this->i18n->trans('order');
			}elseif (false !== strpos($colName, 'idalbaran')) {
				$colName = $this->i18n->trans('delivery-note');
			}elseif (false !== strpos($colName, 'idfactura')) {
				$colName = $this->i18n->trans('invoice');
			}elseif (false !== strpos($colName, 'idproyecto')) {
				$colName = $this->i18n->trans('project');
			}elseif (false !== strpos($colName,'riesgomax')) {
				$colName = ('- ');
				$value = ('-');
			}
			$tableDataAux[] = ['key' => $colName, 'value' => $this->fixValue($value)];
		}

        $title .= ': ' . $model->primaryDescription();
        $this->pdf->ezText("\n" . $this->fixValue($title) . "\n", self::FONT_SIZE + 6);
        $this->newLine();

        $this->insertParallelTable($tableDataAux, '', $tableOptions);
        $this->insertFooter();
        return true;
    }
}