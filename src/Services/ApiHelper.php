<?php

namespace App\Services;

use App\Entity\Team;
use App\Entity\League;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @see \App\Tests\Services\ApiHelperTest
 */
class ApiHelper
{
    /**
     * Config for validate json request
     * 
     */
    protected $_config = [
        'add-league' => [
            'entity' => 'League',
            'param' => ['title'],
            'param-validation' => [
                'title' => [
                    'required' => true,
                    //'unique' => true,
                ]
             ],
        ],
        'get-teams' => [
            'entity' => 'League',
            'param' => ['leagueId'],
            'param-validation' => [
                'leagueId' => [
                    'type' => 'integer',
                    'required' => true,
                ]
             ],
        ],
        'add-team' => [
            'entity' => 'Team',
            'param' => ['title', 'leagueId', 'strip'],
            'param-validation' => [
                'title' => [
                    'required' => true,
                ],
                'leagueId' => [
                    'type' => 'integer',
                    'required' => true,
                ]
             ],
        ],
        'edit-team' => [
            'entity' => 'Team',
            'param' => ['id', 'title', 'leagueId', 'strip'],
            'param-validation' => [
                'title' => [
                    'required' => true,
                ],
                'id' => [
                    'type' => 'integer',
                    'required' => true,
                ],
                'leagueId' => [
                    'type' => 'integer',
                    'required' => true,
                ]
             ],
        ],
        'remove-league' => [
            'entity' => 'League',
            'param' => ['leagueId'],
            'param-validation' => [
                'leagueId' => [
                    'type' => 'integer',
                    'required' => true,
                ]
             ],
        ],
    ];
    
    /***
     * Validate request from api call
     * 
     * @param Request $request
     * @response array
     */
    public function validate(Request $request)
    {
        $action = $request->request->get('action');
        if (!$action || !array_key_exists($action, $this->_config)) {
            return ['status' => false, 'message' => 'No action found'];
        }
                
        // Checking request action params
        if (!$request->request->get($action . '-param')) {
            return ['status' => false, 'message' => 'Missing action param key: ' . $action . '-param'];
        }
        $message = null;
        // Checking request action params using config @see self::_config
        foreach ($this->_config[$action]['param'] as $param) {
            if (!array_key_exists($param, $request->request->get($action . '-param'))) {
                return ['status' => false, 'message' => 'Missing action param key: ' . $param];
            }
            if (isset($this->_config[$action]['param-validation'][$param])) {
                $actionParam = $request->request->get($action . '-param');
                $validator = Validation::createValidator();
                foreach ($this->_config[$action]['param-validation'][$param] as $validation => $validationValue) {
                    $violations = $validator->validate($actionParam[$param], 
                        $this->_setValidationParams($validation, $validationValue)
                    );
                        
                    if (0 !== count($violations)) {
                        // there are errors, now you can show them
                        foreach ($violations as $violation) {
                            $message = "Parameter " . $param . ': ' .  $violation->getMessage();
                            break 3;
                        }
                    }
                }
            }
        }
        
        if ($message) {
            return ['status' => false, 'message' => $message];
        }
        return ['status' => true, 'message' => 'Action performed successfully'];
    }
    
    protected function _setValidationParams($validation, $validationValue)
    {
        $validationConf = '';
        switch ($validation)
        {
            case 'required': 
                $validationConf = ($validationValue) ? new NotBlank() : '';
                break;
            case 'length': 
                $validationConf = new Length(['min' => 5, 'max' => 255]);
                break;  
            case 'type': 
                $validationConf = new Type('integer');
                break; 
            case 'unique': 
                $validationConf = new UniqueEntity(['fields' => 'title']);
                break;
        }
        return [$validationConf];
    }
    
    /***
     * Perform action for api call
     * @param Request $request
     * @param EntityManager $entityManager 
     * @response array
     */
    public function action(Request $request, EntityManager $entityManager)
    {
        $data = [];
        $status = false;
        $action = $request->request->get('action');
        $message = '';
        
        switch ($action) {
            case 'add-league':
                $param = $request->request->get($request->request->get('action') . '-param');
                $league = new League();                
                $league->setTitle($param['title']);                
                $entityManager->persist($league);
                $entityManager->flush();
                if ($league->getId()) {
                    $status = true;
                    $message = 'League added successfully';
                    $data = ['id' => $league->getId()];
                }
                break;
            
            case 'get-teams':
                $param = $request->request->get('get-teams-param');
                $league = $this->_fetchLeagueRepo($entityManager, $param['leagueId']);
                $message = 'League not found with leagueId ' . $param['leagueId'];
                if ($league) {
                    $status = true;
                    $message = 'List of team data for league';
                    $data = $this->_prepareTeamData($league);
                }
                break;
            
            case 'add-team':
            case 'edit-team':
                $response = $this->_storeTeamData($entityManager, $request);                
                $status = $response['status'];
                $message = $response['message'];
                $data = $response['data'];
                break;
            
            case 'remove-league':
                $param = $request->request->get('remove-league-param');
                $league = $this->_fetchLeagueRepo($entityManager, $param['leagueId']);
                $message = 'League not found';
                if ($league) {
                    $status = true;
                    $entityManager->remove($league);
                    $entityManager->flush();
                    $message = 'League removed successfully';
                }
                break;

            default:
                break;
        }
        
        return ['status' => $status, 'message' => $message ,'data' => $data];
    }
    
    /***
     * Prepare data for team
     */
    protected function _prepareTeamData($leagueData)
    {
        $data = [];
        $data['league'] = $leagueData->getTitle();
        $data['teams'] = [];
        foreach ($leagueData->getTeams() as $team) {
            $data['teams'][] = [
                'id' => $team->getId(),
                'title' => $team->getTitle(),
                'strip' => $team->getStrip(),
            ];
        }
        return $data;
    }
    
    protected function _fetchLeagueRepo($entityManager, $leagueId)
    {
        return $entityManager->getRepository(League::class)->find($leagueId);
    }
    
    protected function _fetchTeamRepo($entityManager, $teamId)
    {
        return $entityManager->getRepository(Team::class)->find($teamId);
    }
    
    /***
     * Store team data
     * @param EntityManager $entityManager 
     * @param Request $request 
     * @return boolean
     */
    protected function _storeTeamData(EntityManager $entityManager, $request)
    {
        $param = $request->request->get($request->request->get('action') . '-param');
        $id = null;
        if (isset($param['id'])) {
            $id = $param['id'];
            $team = $this->_fetchTeamRepo($entityManager, $id);
            if (!$team) {
                return ['status' => false, 'message' => 'Team does not exists for id: ' . $id, 'data' => []];
            }
        }
        else {
            $team = new Team();
        }
        $team->setTitle($param['title']);
        $team->setStrip($param['strip']);
        $league = $this->_fetchLeagueRepo($entityManager, $param['leagueId']);
        if (!$league) {
            return ['status' => false, 'message' => 'League does not exists for leagueId: ' . $param['leagueId'], 'data' => []];
        }
        $team->setLeague($league);
        $entityManager->persist($team);
        $entityManager->flush();
        if ($team->getId()) {
            return ['status' => true, 'message' => 'Team action performed successfully', 'data' => ['id' => $team->getId()]];
        }
        return ['status' => false, 'message' => '', 'data' => []];
    }
}

