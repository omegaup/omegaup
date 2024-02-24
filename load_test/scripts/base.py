from demo_util import omegaUpApi
from endpoints import OMEGAUP_ENDPOINTS

class Base:
    def __init__(self, config):
        self.domain = "localhost:8001"

        self.api = omegaUpApi(domain=self.domain)
        self.endpoints = OMEGAUP_ENDPOINTS


    def create_user(self, username, email, password):
        endpoint = self.endpoints.get("create_user")
        payload = {
            "username": username,
            "email": email,
            "password": password
        }
        res = self.api.call(endpoint, "post", json_body=payload)
        assert res.status_code == 200