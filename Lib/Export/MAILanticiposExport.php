<?php

namespace FacturaScripts\Plugins\Anticipos\Lib\Export;

use FacturaScripts\Core\Lib\Export\PDFExport as MailAnticipos;
use Symfony\Component\HttpFoundation\Response;

class MAILanticiposExport extends MailAnticipos
{
	protected $sendParams = [];
	
    public function addModelPage($model, $columns, $title = ''): bool
    {
		$this->sendParams['modelClassName'] = $model->modelClassName();
        $this->sendParams['modelCode'] = $model->primaryColumnValue();
		
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
			}elseif (false !== strpos($colName,'idempresa')) {
				continue;
			}elseif (false !== strpos($colName,'riesgomax')) {
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
	
	public function show(Response &$response)
    {
        $fileName = $this->getFileName() . '_mail_' . time() . '.pdf';
        $filePath = \FS_FOLDER . '/MyFiles/' . $fileName;
        if (false === \file_put_contents($filePath, $this->getDoc())) {
            $this->toolBox()->i18nLog()->error('folder-not-writable');
            return;
        }

        $this->sendParams['fileName'] = $fileName;
        $response->headers->set('Refresh', '0; SendMail?' . \http_build_query($this->sendParams));
    }
}