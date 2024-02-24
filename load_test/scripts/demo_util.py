from urllib.parse import urljoin

import requests

class omegaUpApi():
    def __init__(self, domain="localhost:8001"):
        self.domain = domain

    def call(
        self,
        path: str,
        request_method: str,
        query_string: dict = None,
        json_body: dict = None,
        headers: dict = None,
        **kwargs,
    ):
        request_method_mapping = {
            "get": requests.get,
            "post": requests.post,
            "put": requests.put,
            "patch": requests.patch,
            "delete": requests.delete,
        }
        if request_method.lower() not in request_method_mapping.keys():
            raise Exception(f"request_method: {request_method} not found")
        url = urljoin("http://" + self.domain, path)
        req_func = request_method_mapping[request_method]
        response = req_func(
            url,
            headers=headers,
            json=json_body,
            params=query_string,
            verify=False,
            **kwargs,
        )
        return response