<?php

namespace app\components\TabularInput;


use Yii;
use yii\base\InvalidConfigException;
use unclead\multipleinput\renderers\TableRenderer;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;

/**
 * Widget for rendering multiple input for an attribute of model.
 *
 * @author Eugene Tupikov <unclead.nsk@gmail.com>
 */
class CustomMultipleInput extends MultipleInput
{
    /**
     * @var array
     */
    public $models = [];
    /**
     * @var bool
     */
    public $showFooter = false;

    /**
     *
     * @var array attribute names
     */
    public $attributes = [];
   /**
     * @return object|TableRenderer
     * @throws InvalidConfigException
     */
    public function createRenderer()
    {
        if($this->sortable) {
            $drag = [
                'name'  => 'drag',
                'type'  => MultipleInputColumn::TYPE_DRAGCOLUMN,
                'headerOptions' => [
                    'style' => 'width: 20px;',
                ]
            ];

            array_unshift($this->columns, $drag);
        }

        $available_themes = [
            self::THEME_BS,
            self::THEME_DEFAULT
        ];

        if (!in_array($this->theme, $available_themes, true)) {
            $this->theme = self::THEME_BS;
        }

        /**
         * set default icon map
         */
        $iconMap = array_key_exists($this->iconSource, $this->iconMap)
            ? $this->iconMap[$this->iconSource]
            : $this->iconMap[self::ICONS_SOURCE_GLYPHICONS];

        $config = [
            'id'                => $this->getId(),
            'columns'           => $this->columns,
            'min'               => $this->min,
            'max'               => $this->max,
            'attributeOptions'  => $this->attributeOptions,
            'data'              => $this->data,
            'columnClass'       => $this->columnClass !== null ? $this->columnClass : MultipleInputColumn::className(),
            'allowEmptyList'    => $this->allowEmptyList,
            'addButtonPosition' => $this->addButtonPosition,
            'rowOptions'        => $this->rowOptions,
            'context'           => $this,
            'form'              => $this->form,
            'sortable'          => $this->sortable,
            'enableError'       => $this->enableError,
            'cloneButton'       => $this->cloneButton,
            'extraButtons'      => $this->extraButtons,
            'layoutConfig'      => $this->layoutConfig,
            'iconMap'           => $iconMap,
            'theme'             => $this->theme,
            'prepend'           => $this->prepend,
        ];

        if ($this->showGeneralError) {
            $config['jsExtraSettings'] = [
                'showGeneralError' => true
            ];
        }

        if ($this->removeButtonOptions !== null) {
            $config['removeButtonOptions'] = $this->removeButtonOptions;
        }

        if ($this->addButtonOptions !== null) {
            $config['addButtonOptions'] = $this->addButtonOptions;
        }

        if ($this->cloneButtonOptions !== null) {
            $config['cloneButtonOptions'] = $this->cloneButtonOptions;
        }

        if($this->showFooter){
            $config['class'] = $this->rendererClass ?: CustomTableRender::className();
            $config['attributes'] = $this->attributes;
        }else{
            $config['class'] = $this->rendererClass ?: TableRenderer::className();
        }

        return Yii::createObject($config);
    }
}
