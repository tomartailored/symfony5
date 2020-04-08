Use symfony commands to setup database and migration

API DOCUMENTATION

Fetch Token (JWT)
example : 
URL: http://127.0.0.1:8000/api/login_check
Method : POST
Headers : 
	Key : Content-Type  Value : application/json
Request Body : 
                {
                  "username":"rahul",
                  "password":"rahul123",
                  "action" : "league_list"
                }
Respose : 
                { 
                  "token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE1ODYyNTE5NTMsImV4cCI6MTU4NjI1NTU1Mywicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoicmFodWwifQ.Sd-Gep09SClZI1crXgFSU4kwgPBWAjk4hM0-fP4veBP45JvoGiYeg3nUSYxXrMiKLI7xa6C-ibsqe6c3l370ZDoy8eYAq7HMi5XTvH4k..................” 
                }
================================================================================================================================
1. Get a list of football teams in a single league
URL : http://127.0.0.1:8000/api/league-team-list
Method : GET
Body : 
                {
                  "action":"get-teams",
                  "get-teams-param": {
                    "leagueId": 1
                  }
                }
Response : 
                {
                  "status": 200,
                  "success":
                  {
                    "status": true,
                    "data": 
                    {
                      "league": "League A",
                      "teams": [
                      {
                        "id": 1,
                        "title": "Team A",
                        "strip": "red"
                      }]
                    }
                  }
                }
================================================================================================================================
2. Create a football team
URL : http://127.0.0.1:8000/api/add-team
Method : POST
BODY :
                {
                  "action":"add-team",
                  "add-team-param": {
                    "title": "test League team A",
                    "leagueId":1,
                    "strip": "LOL"
                  }
                }

Response : 
                {
                    "status": 200,
                    "success": {
                        "status": true,
                        "message": "",
                        "data": []
                    }
                }
================================================================================================================================
3. Modify all attributes of a football team
URL : http://127.0.0.1:8000/api/update-team
Method : PUT
BODY :
                {
                  "action":"edit-team",
                  "edit-team-param": {
                    "id": 1,
                    "title": "test League team A",
                    "leagueId":1,
                    "strip": "LOL"
                  }
                }

Response : 
                {
                    "status": 200,
                    "success": {
                        "status": true,
                        "message": "",
                        "data": []
                    }
                }
================================================================================================================================
4. Delete a football league
URL : http://localhost:8000/api/delete-league
Method : DELTE
BODY :
                {
                  "action":"remove-league",
                  "remove-league-param": {
                    "leagueId":1
                  }
                }

Response : 
                {
                    "status": 200,
                    "success": {
                        "status": true,
                        "message": "League removed successfully",
                        "data": []
                    }
                }
================================================================================================================================
