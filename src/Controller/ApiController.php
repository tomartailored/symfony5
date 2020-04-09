<?php
namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use \App\Services\ApiHelper;
use Doctrine\ORM\EntityManagerInterface;

/**
* @Route("/api", name="api.")
*/
class ApiController extends AbstractController 
{

    /**
     * @var integer HTTP status code - 200 (OK) by default
     */
    protected $statusCode = 200;

    /**
     * Fetches league teams
     *      
     * @Route("/league/{id}", name="league_team_list", methods={"GET"})
     * @param Request $request
     * @param ApiHelper $apiHelper
     * @return json
     */
    public function leagueTeamListAction($id, Request $request, ApiHelper $apiHelper)
    {
        $request->request->add([
            'action' => 'get-teams',
            'get-teams-param' => ['leagueId' => (int) $id]
        ]);
        
        $response = $this->_validateApiCall($request, $apiHelper, 'get-teams');
        if (!$response['status']) {
            return $this->respondValidationError($response['message']);
        }
        return $this->respondWithSuccess($response);
    }
    
    /**
     * Add league
     *      
     * @Route("/league", name="add_league", methods={"POST"})
     * @param Request $request
     * @param ApiHelper $apiHelper
     * @return json
     */
    public function addLeagueAction(Request $request, ApiHelper $apiHelper)
    {
        $data = json_decode($request->getContent(), true);
        $request->request->add([
            'action' => 'add-league',
            'add-league-param' => ['title' => isset($data['title']) ? $data['title'] : '',]
        ]);
        $response = $this->_validateApiCall($request, $apiHelper, 'add-league');
        if (!$response['status']) {
            return $this->respondValidationError($response['message']);
        }
        return $this->respondWithSuccess($response);
    }
    
    /**
     * Add team for league
     *      
     * @Route("/league/{id}", name="add_team", methods={"POST"})
     * @param Request $request
     * @param ApiHelper $apiHelper
     * @return json
     */
    public function addTeamAction($id, Request $request, ApiHelper $apiHelper)
    {
        $data = json_decode($request->getContent(), true);
        $request->request->add([
            'action' => 'add-team',
            'add-team-param' => [
                'title' => isset($data['title']) ? $data['title'] : '',
                'leagueId' => (int) $id,
                'strip' => isset($data['strip']) ? $data['strip'] : '',
            ]
        ]);
        $response = $this->_validateApiCall($request, $apiHelper, 'add-team');
        if (!$response['status']) {
            return $this->respondValidationError($response['message']);
        }
        return $this->respondWithSuccess($response);
    }
    
    /**
     * Edit team for league
     *      
     * @Route("/league/{id}/team/{teamId}", name="update_team", methods={"PUT"})
     * @param Request $request
     * @param ApiHelper $apiHelper
     * @return json
     */
    public function updateTeamAction($id, $teamId, Request $request, ApiHelper $apiHelper)
    {
        $data = json_decode($request->getContent(), true);
        $request->request->add([
            'action' => 'edit-team',
            'edit-team-param' => [
                'id' => (int) $teamId,
                'title' => isset($data['title']) ? $data['title'] : '',
                'leagueId' => (int) $id,
                'strip' => isset($data['strip']) ? $data['strip'] : '',
            ]
        ]);
        $response = $this->_validateApiCall($request, $apiHelper, 'edit-team');
        if (!$response['status']) {
            return $this->respondValidationError($response['message']);
        }
        return $this->respondWithSuccess($response);
    }
    
    /**
     * Remove league
     *      
     * @Route("/league/{id}", name="delete_team", methods={"DELETE"})
     * @param Request $request
     * @param ApiHelper $apiHelper
     * @return json
     */
    public function deleteLeagueAction($id, Request $request, ApiHelper $apiHelper)
    {
        $request->request->add([
            'action' => 'remove-league',
            'remove-league-param' => ['leagueId' => (int) $id]
        ]);
        $response = $this->_validateApiCall($request, $apiHelper, 'remove-league');
        if (!$response['status']) {
            return $this->respondValidationError($response['message']);
        }
        return $this->respondWithSuccess($response);
    }
    
    /**
     * Edit team for league
     *      
     * @Route("/edit-team", name="edit_team", methods={"PUT"})
     * @param Request $request
     * @param ApiHelper $apiHelper
     * @param string $action
     * @return array
     */
    protected function _validateApiCall(Request $request, ApiHelper $apiHelper, $action)
    {
        
        if ($request->request->get('action') != $action) {
            return ['status' => false, 'message' => 'Wrong method or action'];
        }
        $response = $apiHelper->validate($request);
        
        if (!$response['status']) {
            return $response;
        }
        $entityManager = $this->getDoctrine()->getManager();
        $response = $apiHelper->action($request, $entityManager);
        return $response;        
    }
    
    /**
     * Gets the value of statusCode.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the value of statusCode.
     *
     * @param integer $statusCode the status code
     *
     * @return self
     */
    protected function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Returns a JSON response
     *
     * @param array $data
     * @param array $headers
     *
     * @return JsonResponse
     */
    public function response($data, $headers = [])
    {
        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }

    /**
     * Sets an error message and returns a JSON response
     *
     * @param string $errors
     * @param $headers
     * @return JsonResponse
     */
    public function respondWithErrors($errors, $headers = [])
    {
        $data = [
            'status' => $this->getStatusCode(),
            'errors' => $errors,
        ];

        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }

    /**
     * Sets an error message and returns a JSON response
     *
     * @param string $success
     * @param $headers
     * @return JsonResponse
     */
    public function respondWithSuccess($success, $headers = [])
    {
        $data = [
            'status' => $this->getStatusCode(),
            'success' => $success,
        ];

        return new JsonResponse($data, $this->getStatusCode(), $headers);
    }

    /**
     * Returns a 401 Unauthorized http response
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondUnauthorized($message = 'Not authorized!')
    {
        return $this->setStatusCode(401)->respondWithErrors($message);
    }

    /**
     * Returns a 422 Unprocessable Entity
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondValidationError($message = 'Validation errors')
    {
        return $this->setStatusCode(422)->respondWithErrors($message);
    }

    /**
     * Returns a 404 Not Found
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondNotFound($message = 'Not found!')
    {
        return $this->setStatusCode(404)->respondWithErrors($message);
    }

    /**
     * Returns a 201 Created
     *
     * @param array $data
     *
     * @return JsonResponse
     */
    public function respondCreated($data = [])
    {
        return $this->setStatusCode(201)->response($data);
    }

    // this method allows us to accept JSON payloads in POST requests
    // since Symfony 4 doesn’t handle that automatically:

    protected function transformJsonBody(\Symfony\Component\HttpFoundation\Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }

}
