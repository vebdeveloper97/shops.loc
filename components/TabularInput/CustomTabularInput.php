<?php

namespace app\components\TabularInput;


use Yii;
use yii\base\InvalidConfigException;
use unclead\multipleinput\renderers\TableRenderer;
use unclead\multipleinput\TabularInput;
use unclead\multipleinput\TabularColumn;

/**
 * Widget for rendering multiple input for an attribute of model.
 *
 * @author Eugene Tupikov <unclead.nsk@gmail.com>
 */
class CustomTabularInput extends TabularInput
{
    /**
     * @var bool
     */
    public $showFooter = false;

    /**
     * @var null
     */
    public $id = null;
    /**
     *
     * @var array attribute names
     */
    public $attributes = [];

    public $customFirstRow = [];

    /**
     * @return object|TableRenderer
     * @throws InvalidConfigException
     */
    protected function createRenderer()
    {
        if($this->sortable) {
            $drag = [
                'name'  => 'drag',
                'type'  => TabularColumn::TYPE_DRAGCOLUMN,
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
        $id = $this->getId();
        if(!empty($this->id)){
            $id = $this->id;
        }

        /**
         * set default icon map
         */
        $iconMap = array_key_exists($this->iconSource, $this->iconMap)
            ? $this->iconMap[$this->iconSource]
            : $this->iconMap[self::ICONS_SOURCE_GLYPHICONS];

        $config = [
            'id'                => $id,
            'columns'           => $this->columns,
            'min'               => $this->min,
            'max'               => $this->max,
            'attributeOptions'  => $this->attributeOptions,
            'data'              => $this->models,
            'columnClass'       => $this->columnClass !== null ? $this->columnClass : TabularColumn::className(),
            'allowEmptyList'    => $this->allowEmptyList,
            'rowOptions'        => $this->rowOptions,
            'addButtonPosition' => $this->addButtonPosition,
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
            $config['attributes'] = $this->attributes;
            $config['customFirstRow'] = $this->customFirstRow;
            $config['class'] = $this->rendererClass ?: CustomTableRender::className();
        }else{
            $config['class'] = $this->rendererClass ?: TableRenderer::className();
        }


        return Yii::createObject($config);
    }
}
