<?php

/**
 *
 *
 * PHP version 5
 *
 * @category
 * @package
 * @subpackage 
 * @author     Tóth Norbert <tothnorbert.zalalovo@gmail.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 * @link       
 */

class sfWidgetFormFCBKComplete extends sfWidgetForm
{

    /**
     * Configure the widget
     *
     * @param array $options
     * @param array $attributes 
     */
    public function configure($options = array(), $attributes = array())
    {
        $this->addOption('choices', array());
        $this->addOption('maxitems', 1);
        $this->addOption('height', 2);
        $this->addOption('json_url', '');
        $this->addOption('complete_text', 'Kezdjen el írni...');
        $this->addRequiredOption('model');
        $this->addRequiredOption('title');
        
        $this->addOption('template.html', '{input.search}');

        $this->addOption('template.javascript',
            '<script type="text/javascript">
                jQuery("#{field.id}").fcbkcomplete({
                    maxitems: {maxitems},
                    height: {height},
                    json_url: "{json_url}",
                    complete_text: "{complete_text}"
                })
                
                var valueArray = {update_vars};
                if (valueArray !== 0) {
                    valueArray.forEach(function(el, ind, ar){
                        jQuery("#{field.id}").trigger("addItem",[{"title": el.title, "value": el.id}]);
                    });
            }
             </script>'
        );
    }//end configure()


    /**
     *
     * @param string $name
     * @param mixed $value
     * @param array $attributes
     * @param array $errors
     * @return string
     */
    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        $template_vars = array(
            '{maxitems}'      => $this->getOption('maxitems'),
            '{height}'        => $this->getOption('height'),
            '{json_url}'      => $this->getOption('json_url'),
            '{complete_text}' => $this->getOption('complete_text'),
            '{field.id}'      => $this->generateId($name),
            '{update_vars}'   => false,
            '{title}'         => $this->getOption('title')
        );

        $value = $value === null ? 'null' : $value;

        if (empty($value) === false) {
            $vars = Doctrine_Core::getTable($this->getOption('model'))->createQuery('m')->select('m.id, m.' . $this->getOption('title') . ' as title')->whereIn('m.id', $value)->fetchArray();
            $template_vars['{update}'] = true;
            $template_vars['{update_vars}'] = json_encode($vars);
//            $template_vars['sql'] = Doctrine_Core::getTable($this->getOption('model'))->createQuery('m')->select('m.id, m.' . $this->getOption('title') . ' as title')->whereIn('m.id', $value)->getSqlQuery();
        } else {
            $template_vars['{update_vars}'] = 0;
        }//end if

        $options = array();
        foreach ($this->getOption('choices') as $key => $option) {
            $attributes = array('value' => self::escapeOnce($key));
            if ($key == $value) {
                $attributes['selected'] = 'selected';
            }//end if

            $options[] = $this->renderContentTag(
                'option', self::escapeOnce($option), $attributes
            );
        }//end foreach
        
        $template_vars['{input.search}'] = $this->renderContentTag(
            'select', "\n" . implode("\n", $options) . "\n", array_merge(array('name' => $name), $attributes
        ));
        
        // merge templates and variables
        return strtr(
          $this->getOption('template.html').$this->getOption('template.javascript'),
          $template_vars
        );
    }//end render()


    /**
     *
     * @return array
     */
    public function getJavaScripts()
    {
        return array(
            'http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js',
            'FCBKcomplete/jquery.fcbkcomplete.js'
        );
    }//end getJavaScripts()


    /**
     *
     * @return array
     */
    public function getStylesheets()
    {
        return array(
            '/js/FCBKcomplete/style.css' => 'screen'
        );
    }//end getStylesheets()

}//end class
