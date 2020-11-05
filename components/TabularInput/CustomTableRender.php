<?php

namespace app\components\TabularInput;

use yii\base\InvalidConfigException;
use yii\db\ActiveRecordInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use unclead\multipleinput\components\BaseColumn;
use unclead\multipleinput\renderers\TableRenderer;

/**
 * Class CustomTableRenderer
 * @package unclead\multipleinput\renderers
 */
class CustomTableRender extends TableRenderer
{
    public $attributes;
    public $customFirstRow = [];

    /**
     * @return mixed|string
     * @throws InvalidConfigException
     */
    protected function internalRender()
    {
        $content = [];

        if ($this->hasHeader()) {
            $content[] = $this->renderHeader();
        }
        if(!empty($this->customFirstRow)){
            $content[] = $this->renderCustomFirstRow();
        }
        $content[] = $this->renderBody();
        $content[] = $this->renderFooter();

        $options = [];
        Html::addCssClass($options, 'multiple-input-list');

        if ($this->isBootstrapTheme()) {
            Html::addCssClass($options, 'table table-condensed table-renderer');
        }

        $content = Html::tag('table', implode("\n", $content), $options);

        return Html::tag('div', $content, [
            'id' => $this->id,
            'class' => 'multiple-input'
        ]);
    }

    /**
     * Renders the footer.
     *
     * @return string
     */
    public function renderFooter()
    {
        $columnsCount = 0;
        $cells = [];

        foreach ($this->columns as $column) {
            if (!$column->isHiddenInput()) {
                $footerId = '';
                $content = '&nbsp;';
                if(isset($this->attributes[$columnsCount])){
                    $footerId = $this->attributes[$columnsCount]['id'];
                    $content = $this->attributes[$columnsCount]['value'];
                }
                $cells[$columnsCount] = Html::tag('td', $content,['class' => "multipleTabularInput-footer", 'id' => $footerId]);
                $columnsCount++;
            }
        }

        if ($this->cloneButton) {
            $columnsCount++;
            //$cells[$columnsCount] = Html::tag('td', '&nbsp;',['class' => "multipleTabularInput-footer clone_btn_footer"]);
        }

        return Html::tag('tfoot', Html::tag('tr', implode("\n", $cells)),['class' => 'multipleTabularInput-tfoot']);
    }

    public function renderCustomFirstRow(){

        $columnsCount = 0;
        $cells = [];

        foreach ($this->customFirstRow as $column) {
            $cells[$columnsCount] = Html::tag('th', $column['data'],
                ['class' => "multipleTabularInput-custom-first-row",
                    'id' => $column['id']]);
            $columnsCount++;
        }

        return Html::tag('thead', Html::tag('tr', implode("\n", $cells),['class' => 'multipleTabularInput-tr-custom-first-row']));
    }

    /**
     * Check that at least one column has a header.
     *
     * @return bool
     */
    private function hasHeader()
    {
        if ($this->min === 0 || $this->isAddButtonPositionHeader()) {
            return true;
        }

        foreach ($this->columns as $column) {
            /* @var $column BaseColumn */
            if ($column->title) {
                return true;
            }
        }

        return false;
    }
}
