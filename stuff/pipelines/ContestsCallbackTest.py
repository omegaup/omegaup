#!/usr/bin/python3

'''Processing contest messages for testing.'''

import dataclasses
from typing import Optional
import ContestsCallback
import mysql.connector
import mysql.connector.cursor
import pika
from verification_code import generate_code


@dataclasses.dataclass
class Certificate:
    '''A dataclass for certificate.'''
    certificate_type: str
    contest_id: int
    verification_code: str
    contest_place: Optional[int]
    username: str


class ContestsCallbackTest:
    '''Contests callback'''
    def __init__(self,
                 dbconn: mysql.connector.MySQLConnection,
                 api_token: str,
                 url: str):
        '''Contructor for contest callback for testing'''
        self.dbconn = dbconn
        self.api_token = api_token
        self.url = url

    def __call__(self,
                 channel: pika.adapters.blocking_connection.BlockingChannel,
                 _method: pika.spec.Basic.Deliver,
                 _properties: pika.spec.BasicProperties,
                 _body: bytes) -> None:
        '''Function to call the original callback'''
        ContestsCallback.ContestsCallback(self.dbconn,
                                          self.api_token,
                                          self.url)
        channel.close()
    

# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
