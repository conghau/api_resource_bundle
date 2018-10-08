<?php

namespace conghau\Bundle\ApiResource\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use conghau\Bundle\ApiResourceBundle\Helper\Helper;

/**
 * Class ApiResourceController
 * @package conghau\ApiResource\Controller
 */
class ApiResourceController extends BaseApiController
{

    /**
     *### Json Post Example ###
     *     {
     *        "filter": {},
     *        "pageNumber": 1,
     *        "pageSize": 1,
     *        "orderBy": "id",
     *        "orderDirection": "ASC",
     *     },
     * @ApiDoc(
     *  description="Search Action Generate by Api Resource",
     *  statusCodes={
     *     200="Returned when successful",
     *     500="Returned when error",
     *   }
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        try {
            $entityClass = $this->getEntityClass($request);
            if (empty($entityClass)) {
                return $this->createApiResponse([]);
            }
            $searchPost = $request->request->all();
            $qb = $this->getDoctrine()->getRepository($entityClass)->searchWith(
                $searchPost['filter'] ?? [],
                $searchPost['orderBy'] ?? 'id',
                $searchPost['orderDirection'] ?? 'ASC'
            );

            $data = $this->container->get('pagination_factory')->createCollection(
                $qb,
                $searchPost['pageSize'] ?? 10,
                $searchPost['pageNumber'] ?? 1
            );

            return $this->createApiResponse($data);
        } catch
        (\Throwable $t) {
            $this->writeErrorLog('ApiResourceController', 'searchAction', $t->getMessage());

            return $this->createApiResponse(['code' => 500, 'message' => $t->getMessage()]);
        } catch (\Exception $e) {
            $this->writeErrorLog('ApiResourceController', 'searchAction', $e->getMessage());

            return $this->createApiResponse(['code' => 500, 'data' => $e->getMessage()]);
        }
    }

    /**
     * @ApiDoc(
     *  description="Create Action Generate by Api Resource",
     *  statusCodes={
     *     200="Returned when successful",
     *     500="Returned when error",
     *   }
     * )
     * @param Request $request
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        try {
            $entityClass = $this->getEntityClass($request);
            if (empty($entityClass)) {
                return $this->createApiResponse([]);
            }

            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository($entityClass);

            $dataPost = $request->request->all();
            $entity = $this->mapDataOnEntity($dataPost, new $entityClass);
            //add more data
            $entity->setCreatedBy($this->loggerUser->getUsername());

            //validator
            $validator = $this->get('validator');
            $errors = $validator->validate($entity);
            if (count($errors) > 0) {
                $error = [];
                foreach ($errors as $violation) {
                    array_push($error, $violation->getMessage());
                }

                return new JsonResponse(['code' => 500, 'message' => $error]);
            }

            $repo->save($entity);

            return $this->createApiResponse(['code' => 200, 'data' => $entity]);

        } catch (\Throwable $t) {
            $this->writeErrorLog('ApiResourceController', 'createAction', $t->getMessage());

            return $this->createApiResponse(['code' => 500, 'message' => $t->getMessage()]);
        } catch (\Exception $e) {
            $this->writeErrorLog('ApiResourceController', 'createAction', $e->getMessage());

            return $this->createApiResponse(['code' => 500, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @ApiDoc(
     *  description="Update Action Generate by Api Resource",
     *  statusCodes={
     *     200="Returned when successful",
     *     500="Returned when error",
     *   }
     * )
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request, int $id)
    {
        try {
            $entityClass = $this->getEntityClass($request);
            if (empty($entityClass)) {
                return $this->createApiResponse([]);
            }

            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository($entityClass);

            $currentEntity = $repo->find($id);

            if (empty($currentEntity)) {
                throw new \Exception('api_resource.not_found');
            }

            $dataPost = $request->request->all();
            $entity = $this->mapDataOnEntity($dataPost, $currentEntity);
            //add more data
            $entity->setUpdatedBy($this->loggerUser->getUsername());

            //validator
            $validator = $this->get('validator');
            $errors = $validator->validate($entity);
            if (count($errors) > 0) {
                $error = [];
                foreach ($errors as $violation) {
                    array_push($error, $violation->getMessage());
                }

                return new JsonResponse(['code' => 500, 'message' => $error]);
            }

            $repo->update($entity);

            return $this->createApiResponse(['code' => 200, 'data' => $entity]);

        } catch (\Throwable $t) {
            $this->writeErrorLog('ApiResourceController', 'updateAction', $t->getMessage());

            return $this->createApiResponse(['code' => 500, 'message' => $t->getMessage()]);
        } catch (\Exception $e) {
            $this->writeErrorLog('ApiResourceController', 'updateAction', $e->getMessage());

            return $this->createApiResponse(['code' => 500, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @ApiDoc(
     *  description="Delete Action Generate by Api Resource",
     *  statusCodes={
     *     200="Returned when successful",
     *     500="Returned when error",
     *   }
     * )
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, int $id)
    {
        try {
            $entityClass = $this->getEntityClass($request);
            if (empty($entityClass)) {
                return $this->createApiResponse([]);
            }

            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository($entityClass);

            $currentEntity = $repo->find($id);

            if (empty($currentEntity)) {
                throw new \Exception($this->get('translator')->trans('api_resource.not_found'));
            }

            $repo->delete($currentEntity);

            return $this->createApiResponse(['code' => 200, 'data' => $currentEntity]);
        } catch (\Throwable $t) {
            $this->writeErrorLog('ApiResourceController', 'deleteAction', $t->getMessage());

            return $this->createApiResponse(['code' => 500, 'message' => $t->getMessage()]);
        } catch (\Exception $e) {
            $this->writeErrorLog('ApiResourceController', 'deleteAction', $e->getMessage());

            return $this->createApiResponse(['code' => 500, 'message' => $e->getMessage()]);
        }
    }

    /**
     *
     * @ApiDoc(
     *  description="Detail Action Generate by Api Resource",
     *  statusCodes={
     *     200="Returned when successful",
     *     500="Returned when error",
     *   }
     * )
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(Request $request, int $id)
    {
        try {
            $entityClass = $this->getEntityClass($request);
            if (empty($entityClass)) {
                return $this->createApiResponse([]);
            }

            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository($entityClass);

            $currentEntity = $repo->find($id);

            if (empty($currentEntity)) {
                throw new \Exception('NOT_FOUND');
            }

            return $this->createApiResponse(['code' => 200, 'data' => $currentEntity]);


        } catch (\Throwable $t) {
            $this->writeErrorLog('ApiResourceController', 'deleteAction', $t->getMessage());

            return $this->createApiResponse(['code' => 500, 'message' => $t->getMessage()], 500);
        } catch (\Exception $e) {
            $this->writeErrorLog('ApiResourceController', 'deleteAction', $e->getMessage());

            return $this->createApiResponse(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function getEntityClass(Request $request)
    {
        $url = $request->getRequestUri();
        $config = $this->container->getParameter('tch_api_resource.config');
        $resources = $config['resources'] ?? [];
        foreach ($resources as $key => $value) {
            $key = Helper::convertWithDash($key);
            if (strpos($url, "/$key/") !== false) {
                return $value['entity'];
            }
        }

        return '';
    }
}
