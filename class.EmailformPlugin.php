<?php
require_once (INCLUDE_DIR . 'class.signal.php');
require_once ('config.php');

class EmailformPlugin extends Plugin {
	const DEBUG = TRUE;
	/**
	 * Which config to use (in config.php)
	 *
	 * @var string
	 */
	public $config_class = 'EmailformPluginConfig';
	
	/**
	 * Run on every instantiation of osTicket..
	 * needs to be concise
	 *
	 * {@inheritdoc}
	 *
	 * @see Plugin::bootstrap()
	 */
	function bootstrap() {
		Signal::connect ( 'ticket.created', function (Ticket $ticket) {
			if (self::DEBUG) {
				error_log ( "Ticket detected, source is: " . $ticket->getSource() . "." );
			}
			if ($ticket->getSource() == "Email"){
				$this->populateFields($ticket);
			}
		});
	}
	
	/**
	 * Adds a form with fields that can be automaticly populated using regex.
	 *
	 * @param Ticket $ticket
	 */
	private function populateFields(Ticket $ticket) {
		$config = $this->getConfig();
		
		// Have a default form been configured?
		if(($form = DynamicForm::lookup($config->get('emailform-defaúlt-form')))){
			
			//Create a new entry of the form.
			$f = $form->instanciate();
			
			//Assign the entry to the ticket.
			$f->setTicketId($ticket->getId());
			
			//Find the first threadentry of ticket (should be the original email).
			$body = $ticket->getThread()->getEntries()[0]->getBody()->getClean();
			
			//Iterate over all fields in the entry.
			foreach ($f->getFields() as $field){
				
				//Does the regex designated to the field match anything in the body?
				if($config->exists('emailform-' . $field->get('label')) 
					&& preg_match("/" . $config->get('emailform-' 
					. $field->get('label')) . "/", $body, $matches)){
					
					//Add the first match to the entry.
					$f->setAnswer($field->get('name'), $matches[0]);
				}
			}
			
			//Save all changes to the entry to the database.
			$f->save();
		}
	}
	
	/**
	 * Required stub.
	 *
	 * {@inheritdoc}
	 *
	 * @see Plugin::uninstall()
	 */
	function uninstall() {
		$errors = array ();
		parent::uninstall ( $errors );
	}
	
	/**
	 * Plugins seem to want this.
	 */
	public function getForm() {
		return array ();
	}
}


