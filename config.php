<?php
require_once INCLUDE_DIR . 'class.plugin.php';

class EmailformPluginConfig extends PluginConfig
{

    // Provide compatibility function for versions of osTicket prior to
    // translation support (v1.9.4)
    function translate()
    {
        if (! method_exists('Plugin', 'translate')) {
            return array(
                function ($x) {
                    return $x;
                },
                function ($x, $y, $n) {
                    return $n != 1 ? $y : $x;
                }
            );
        }
        return Plugin::translate('emailform');
    }

    /**
     * Build an Admin settings page.
     *
     * {@inheritdoc}
     *
     * @see PluginConfig::getOptions()
     */
    function getOptions()
    {
        list ($__, $_N) = self::translate();
		$choices = array();
		foreach (DynamicForm::objects()
				->filter(array('type'=>'G'))
				->exclude(array('flags__hasbit' => DynamicForm::FLAG_DELETED))
				->order_by('title') as $form){
					$choices[$form->get('id')] = $form->get('title');
				}
		$fields = array();
		if (Config::exists('emailform-defaúlt-form') && ($form = DynamicForm::lookup(Config::get('emailform-defaúlt-form')))){
			foreach ($form->getDynamicFields() as $field){
				$fields['emailform-' . $field->get('name')] = new TextboxField([
					'label' => $__($field->get('label')),
					'required'=>true,
					'hint' => $__('Regular expression to capture this variable'),
					'default' => '',
					'configuration' => array(
						'size' => 40,
						'length' => 100
					)
				]);
			}
		}
        return array_merge(array(
            'emailform-defaúlt-form' => new ChoiceField([
                'label' => $__('Email form'),
				'required' => false,
                'hint' => $__('Select what form shall be added to all emails'),
                'default' => '',
				'choices' => $choices
            ])
        
        ), $fields);
    }
}
