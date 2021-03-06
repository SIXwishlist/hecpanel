<?php

/**
 *  HE cPanel -- Hosting Engineers Control Panel
 *  Copyright (C) 2015  Dynamictivity LLC (http://www.hecpanel.com)
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU Affero General Public License as
 *   published by the Free Software Foundation, either version 3 of the
 *   License, or (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Affero General Public License for more details.
 *
 *   You should have received a copy of the GNU Affero General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
?>

<?php

App::uses('InstancesAppController', 'Instances.Controller');

/**
 * InstanceTypes Controller
 *
 * @property InstanceType $InstanceType
 * @property PaginatorComponent $Paginator
 */
class InstanceTypesController extends InstancesAppController {

	/**
	 * Components
	 *
	 * @var array
	 */
	public $components = array(
		'Instances.SEServer'
	);

	/**
	 * admin_index method
	 *
	 * @return void
	 */
	public function admin_index() {
		$this->InstanceType->recursive = 0;
		$this->set('instanceTypes', $this->Paginator->paginate());
	}

	/**
	 * admin_add method
	 *
	 * @return void
	 */
	public function admin_add($gameId = null) {
		if ($gameId < 0 || $gameId === null) {
			throw new NotFoundException(__('Invalid game selected'));
		}
        $this->request->data['InstanceType']['game_id'] = $gameId;
		if ($this->request->is('post')) {
			$this->InstanceType->create();
			if ($this->InstanceType->saveProfile($this->request->data)) {
				$this->setFlash(__('The instance type has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->setFlash(__('The instance type could not be saved. Please, try again.'), 'danger');
			}
		}
		$this->request->data = $this->SEServer->setForm($gameId, $this->request->data, 'InstanceType');
		// Set form configuration options
		$this->set($this->SEServer->getConfigOptions($gameId));
	}

	public function admin_duplicate($id = null) {
		if (!$this->InstanceType->exists($id)) {
			throw new NotFoundException(__('Invalid instance type'));
		}
		$this->request->data['InstanceType'] = $this->InstanceType->findById($id)['InstanceType'];
		unset($this->request->data['InstanceType']['id']);
		$this->request->data['InstanceType']['name'] .= '_Clone';
		$this->InstanceType->create();
		if ($this->request->is(array('post', 'put'))) {
			if ($this->InstanceType->save($this->request->data)) {
				$this->setFlash(__('The instance type has been duplicated.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->setFlash(__('The instance type could not be duplicated. Please, try again.'), 'danger');
				return $this->redirect(array('action' => 'index'));
			}
		}
        $this->autoRender = false;
	}

	/**
	 * admin_edit method
	 *
	 * @throws NotFoundException
	 * @param string $id
	 * @return void
	 */
	public function admin_edit($id = null) {
		if (!$this->InstanceType->exists($id)) {
			throw new NotFoundException(__('Invalid instance type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->InstanceType->saveProfile($this->request->data)) {
				$this->setFlash(__('The instance type has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->setFlash(__('The instance type could not be saved. Please, try again.'), 'danger');
			}
		} else {
			$this->request->data = $this->InstanceType->loadProfile($id);
		}
		$this->request->data = $this->SEServer->setForm($this->request->data['InstanceType']['game_id'], $this->request->data, 'InstanceType');
		// Set form configuration options
		$this->set($this->SEServer->getConfigOptions($this->request->data['InstanceType']['game_id']));
	}

	// Convert old instance type to new
	// TODO: Remove this after use
	public function admin_convert() {
		$this->InstanceType->recursive = -1;
		$instanceTypes = $this->InstanceType->find('all');
		foreach ($instanceTypes as $instanceType) {
			$convertedType['InstanceType'] = array(
				'id' => $instanceType['InstanceType']['id'],
			);
			$this->InstanceType->id = $instanceType['InstanceType']['id'];

			unset($instanceType['InstanceType']['id']);
			unset($instanceType['InstanceType']['name']);
			unset($instanceType['InstanceType']['game_id']);
			unset($instanceType['InstanceType']['created']);
			unset($instanceType['InstanceType']['updated']);
			unset($instanceType['InstanceType']['profile_settings']);

			$convertedType['InstanceType']['profile_settings'] = $instanceType['InstanceType'];
			$this->InstanceType->save($convertedType);
		}
		$this->setFlash(__('The instance types have been converted.'));
		return $this->redirect(array('action' => 'index'));
	}

	/**
	 * admin_delete method
	 *
	 * @throws NotFoundException
	 * @param string $id
	 * @return void
	 */
	public function admin_delete($id = null) {
		$this->InstanceType->id = $id;
		if (!$this->InstanceType->exists()) {
			throw new NotFoundException(__('Invalid instance type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->InstanceType->delete()) {
			$this->setFlash(__('The instance type has been deleted.'));
		} else {
			$this->setFlash(__('The instance type could not be deleted. Please, try again.'), 'danger');
		}
		return $this->redirect(array('action' => 'index'));
	}

}
