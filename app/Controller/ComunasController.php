<?php

	class ComunasController extends AppController {
		
		var $sacaffold;

		public function index() {
			$this->set('comunas', $this->Comuna->find('all'));
		}

		public function view($id) {
			$this->Comuna->id = $id;
			$this->set('comuna', $this->Comuna->read());
		}

		public function add() {
			if ($this->request->is('post')) {
				if ($this->Comuna->save($this->request->data)) {
					$this->Session->setFlash('La comuna ha sido guardada exitosamente.');
					$this->redirect(array('action' => 'index'));
				}
				$this->Session->setFlash('La comuna no fue guardada, intente nuevamente.');
				$this->redirect(array('action' => 'index'));
			}
		}

		function edit($id = null) {
			$this->Comuna->id = $id;
			if ($this->request->is('get')) {
				$this->request->data = $this->Comuna->read();
			} 
			elseif ($this->Comuna->save($this->request->data)) {
					$this->Session->setFlash('La comuna ha sido actualizada exitosamente.');
					$this->redirect(array('action' => 'index'));
			}
			$this->Session->setFlash('La comuna no fue actualizada, intente nuevamente.');
			$this->redirect(array('action' => 'index'));
			
		}

		public function delete($id) {
			if (!$this->request->is('post')) {
				throw new MethodNotAllowedException();
			}
			if ($this->Comuna->delete($id)) {
				$this->Session->setFlash('La comuna ha sido eliminada.');
				$this->redirect(array('action' => 'index'));
			}
			$this->Session->setFlash('La comuna no fue eliminada.');
        	$this->redirect(array('action' => 'index'));
		}

		public function comunas(){
			$this->autoRender = false;

			$comunas = $this->Comuna->find('all');

			echo json_encode($comunas);
		}
	}
?>