<?php

namespace App\Tests\Services;

use \App\Entity\League;
use \App\Entity\Team;
use App\Services\ApiHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ApiHelperTest extends KernelTestCase
{
    
     /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $_entityManager;
    
    /*
     * Store league object
     */
    protected $_league = null;
    
    /*
     * Store team object
     */
    protected $_team = null;


    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->_entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        
        $league = new League();
        $league->setTitle('Test League');
        $team = new Team();
        $team->setTitle('Test Team');
        $team->setStrip('Test Strip');
        $league->addTeam($team);
        $this->_league = $league;
        $this->_team = $team;
        $this->_entityManager->persist($league);
        $this->_entityManager->persist($team);
        $this->_entityManager->flush();
    }
    
    /**
     * @date 202004
     * @group services
     * @dataProvider _dataForTestValidate
     */
    public function testValidate($expected, $requestParam)
    {
        // ARRANGE
        
        $request = new Request();
        if ($requestParam) {
            $request->request->add($requestParam);
        }        
        $apiHelper = new ApiHelper();
        
        // ACT
        
        $response = $apiHelper->validate($request);        
        
        // ASSERT
        
        $this->assertEquals($expected, $response);
    }
    
    public function _dataForTestValidate()
    {                
        $tests = [];
        
        // SCENARIO: Sending nothing in request
        // EXPECTED: status false, No action found
        $tests[] = [
            ['status' => false, 'message' => 'No action found'],
            null
            
        ];
        
        // SCENARIO: Sending action in request, no params set for action
        // EXPECTED: status false, Missing action param        
        $tests[] = [
            ['status' => false, 'message' => 'Missing action param key: get-teams-param'],
            [
                'action' => 'get-teams',                
            ]        
        ];
        
        // SCENARIO: Sending action in request, wrong params set for action param
        // EXPECTED: status false, Missing action param        
        $tests[] = [
            ['status' => false, 'message' => 'Missing action param key: leagueId'],
            [
                'action' => 'get-teams',
                'get-teams-param' => ['id' => 1],                
            ]        
        ];
        
        // SCENARIO: Sending action in request, wrong params data type set for action param
        // EXPECTED: status false, Missing action param        
        $tests[] = [
            ['status' => false, 'message' => 'Parameter leagueId: This value should be of type integer.'],
            [
                'action' => 'get-teams',
                'get-teams-param' => ['leagueId' => 'abcdef'],                
            ]        
        ];
        
        // SCENARIO: Sending action in request, corect params data type set for action param
        // EXPECTED: status true, Missing action param        
        $tests[] = [
            ['status' => true, 'message' => 'Action performed successfully'],
            [
                'action' => 'get-teams',
                'get-teams-param' => ['leagueId' => 1],                
            ]        
        ];
        
        return $tests;        
    }
    
    /**
     * @date 202004
     * @group services
     * @dataProvider _dataForTestAction
     */
    public function testAction($expected, $requestParam, $modifyParam)
    {
        // ARRANGE
        
        if ($modifyParam)
        {
            switch ($requestParam['action'])
            {
                case 'get-teams':
                    $requestParam['get-teams-param']['leagueId'] = $this->_league->getId();
                    break;
                
            }
        }
        
        $request = new Request();
        if ($requestParam) {
            $request->request->add($requestParam);
        }        
        $apiHelper = new ApiHelper();
        
        // ACT
        
        $response = $apiHelper->action($request, $this->_entityManager);        
        
        // ASSERT
        $this->assertEquals($expected['status'], $response['status']);
        $this->assertEquals($expected['message'], $response['message']);
        $this->assertEquals($expected['data']['league'], $response['data']['league']);
        for ( $i = 0 ; $i < count($expected['data']['teams']) ; $i++) {
            $this->assertEquals($expected['data']['teams'][$i]['title'], $response['data']['teams'][$i]['title']);
            $this->assertEquals($expected['data']['teams'][$i]['strip'], $response['data']['teams'][$i]['strip']);
        }
    }
    
    public function _dataForTestAction()
    {                
        $tests = [];
        
        // SCENARIO: Sending correct action and params
        // EXPECTED: status true, message and data
        $tests[] = [
            [
                'status' => true,
                'message' => 'List of team data for league',
                'data' => [
                    'league' => 'Test League',
                    'teams' => [
                        [
                            'title' => 'Test Team',
                            'strip' => 'Test Strip'
                        ]
                    ]
                ],
                
            ],
            [
                'action' => 'get-teams',
                'get-teams-param' => ['leagueId' => ''], 
            ],
            true
        ];
        
        return $tests;        
    }
    
    protected function tearDown(): void
    {
        $this->_entityManager->remove($this->_team);
        $this->_entityManager->remove($this->_league);
        $this->_entityManager->flush();
        
        parent::tearDown();
        
        // doing this is recommended to avoid memory leaks
        $this->_entityManager->close();
        $this->_entityManager = null;
        $this->_league = null;
        $this->_team = null;
    }
}