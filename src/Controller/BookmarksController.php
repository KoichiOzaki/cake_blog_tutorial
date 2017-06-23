<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Log\Log;
/**
 * Bookmarks Controller
 *
 * @property \App\Model\Table\BookmarksTable $Bookmarks
 */
class BookmarksController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
 
     public function isAuthorized($user)
    {
        $action = $this->request->param('action');
    
        // add と index アクションは常に許可します。
        if (in_array($action, ['index', 'add', 'tags'])) {
            return true;
        }
        // その他のすべてのアクションは、id を必要とします。
        if (!$this->request->param('pass.0')) {
            return false;
        }
    
        // ブックマークが現在のユーザーに属するかどうかをチェック
        $id = $this->request->param('pass.0');
        $bookmark = $this->Bookmarks->get($id);
        if ($bookmark->user_id == $user['id']) {
            return true;
        }
        return parent::isAuthorized($user);
    }
 
    public function index()
    {
        $this->paginate = [
            'condition' => [
                'Bookmarks.user_id' => $this->Auth->user('id')
            ]
        ];
        $bookmarks = $this->paginate($this->Bookmarks);

        $this->set(compact('bookmarks'));
        $this->set('_serialize', ['bookmarks']);
    }

    /**
     * View method
     *
     * @param string|null $id Bookmark id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $bookmark = $this->Bookmarks->get($id, [
            'contain' => ['Users', 'Tags']
        ]);

        $this->set('bookmark', $bookmark);
        $this->set('_serialize', ['bookmark']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $bookmark = $this->Bookmarks->newEntity();
        $this->log($this->request->is('post'));
        if ($this->request->is('post')) {
            $bookmark = $this->Bookmarks->patchEntity($bookmark, $this->request->data);
            $bookmark->user_id = $this->Auth->user('id');
            if ($this->Bookmarks->save($bookmark)) {
                $this->Flash->success(__('The bookmark has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bookmark could not be saved. Please, try again.'));
        }
        $tags = $this->Bookmarks->Tags->find('list', ['limit' => 200]);
        $this->set(compact('bookmark', 'users', 'tags'));
        $this->set('_serialize', ['bookmark']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Bookmark id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $bookmark = $this->Bookmarks->get($id, [
            'contain' => ['Tags']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $bookmark = $this->Bookmarks->patchEntity($bookmark, $this->request->data);
            if ($this->Bookmarks->save($bookmark)) {
                $this->Flash->success(__('The bookmark has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bookmark could not be saved. Please, try again.'));
        }
        $users = $this->Bookmarks->Users->find('list', ['limit' => 200]);
        $tags = $this->Bookmarks->Tags->find('list', ['limit' => 200]);
        $this->set(compact('bookmark', 'users', 'tags'));
        $this->set('_serialize', ['bookmark']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Bookmark id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $bookmark = $this->Bookmarks->get($id);
        if ($this->Bookmarks->delete($bookmark)) {
            $this->Flash->success(__('The bookmark has been deleted.'));
        } else {
            $this->Flash->error(__('The bookmark could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function tags()
    {
        $tags = $this->request->param('pass');
        // $tags = $this->request->getParam('pass');
        $bookmarks = $this->Bookmarks->find('tagged', [
            'tags' =>  $tags
        ]);
        $this->set([
            'bookmarks' => $bookmarks,
            'tags' => $tags
        ]);
    }
}
