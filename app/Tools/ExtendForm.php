<?php
	
	namespace App\Tools;
	
	use Nette;
	use Nette\Application\UI\Form;
	class ExtendForm extends Form
	{
		
		public function __construct($parent = null, $name = null)
		{
			parent::__construct($parent, $name);
		}
		
		/**
		 * @param string $label
		 * @param $hint
		 * @return Nette\Utils\Html
		 */
		public function tooltipForm(string $label, $hint = null, $color = 'blue'): Nette\Utils\Html
		{
			return
				Nette\Utils\Html::el()
					->addText($label)
					->addText(' ')
					->addHtml(
						(Nette\Utils\Html::el('div')
							->addAttributes(array('class'=>'tooltip-form', 'data-tip'=>$hint)))
								->addHtml(Nette\Utils\Html::el('i')
							->addAttributes(array('class'=>'question_icon_'.$color.' fa fa-circle-question')))
					);
		}
		
	}