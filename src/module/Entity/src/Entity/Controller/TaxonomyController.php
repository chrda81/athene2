<?php
/**
 * This file is part of Athene2.
 *
 * Copyright (c) 2013-2018 Serlo Education e.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License")
 * you may not use this file except in compliance with the License
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @copyright Copyright (c) 2013-2018 Serlo Education e.V.
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      https://github.com/serlo-org/athene2 for the canonical source repository
 */
namespace Entity\Controller;

use Instance\Manager\InstanceManagerAwareTrait;
use Taxonomy\Manager\TaxonomyManagerAwareTrait;
use Zend\View\Model\ViewModel;

class TaxonomyController extends AbstractController
{
    use InstanceManagerAwareTrait, TaxonomyManagerAwareTrait;

    public function updateAction()
    {
        $entity = $this->getEntity();

        if (!$entity || $entity->isTrashed()) {
            $this->getResponse()->setStatusCode(404);
            return false;
        }

        $instance = $this->getInstanceManager()->getInstanceFromRequest();
        $taxonomy = $this->getTaxonomyManager()->findTaxonomyByName('root', $instance);

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            if (array_key_exists('terms', $data)) {
                foreach ($data['terms'] as $termId => $added) {
                    $term = $this->getTaxonomyManager()->getTerm($termId);

                    if ($added == 1) {
                        $this->getTaxonomyManager()->associateWith($termId, $entity);
                        $event = 'addToTerm';
                    } else {
                        $this->getTaxonomyManager()->removeAssociation($termId, $entity);
                        $event = 'removeFromTerm';
                    }

                    $this->getEventManager()->trigger(
                        $event,
                        $this,
                        [
                            'entity' => $entity,
                            'term'   => $term,
                        ]
                    );
                }

                $this->getEntityManager()->flush();
                $this->redirect()->toUrl(
                    $this->referer()->fromStorage()
                );

                return false;
            }
        } else {
            $this->referer()->store();
        }

        $view = new ViewModel([
            'terms'  => $taxonomy->getChildren(),
            'entity' => $entity,
        ]);
        $view->setTemplate('entity/taxonomy/update');

        return $view;
    }
}
