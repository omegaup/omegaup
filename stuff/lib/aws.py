#!/usr/bin/python3
"""Library to perform calls to AWS."""

import configparser
import datetime
import hashlib
import hmac
import os
import urllib.error
import urllib.parse
import urllib.request
from typing import Dict, Optional, Tuple

_ALGORITHM = 'AWS4-HMAC-SHA256'


def _sign(key: bytes, msg: str) -> bytes:
    """Perform one round of AWS HMAC signature."""
    return hmac.new(key, msg.encode('utf-8'), hashlib.sha256).digest()


def _get_signature_key(key: str, date: str, region_name: str,
                       service_name: str) -> bytes:
    """Create the AWS signature key."""
    key_date = _sign((f'AWS4{key}').encode('utf-8'), date)
    key_region = _sign(key_date, region_name)
    key_service = _sign(key_region, service_name)
    return _sign(key_service, 'aws4_request')


def get_credentials(aws_username: str,
                    filename: Optional[str] = None) -> Tuple[str, str]:
    """Get the AWS credentials from ~/.aws/credentials."""
    if filename is None:
        filename = os.path.join(os.environ['HOME'], '.aws/credentials')

    parser = configparser.ConfigParser()
    parser.read(filename)
    return (parser.get(aws_username, 'aws_access_key_id'),
            parser.get(aws_username, 'aws_secret_access_key'))


def request(access_key: str, secret_key: str, region: str, service: str,
            request_parameters: Dict[str, str]) -> None:
    """Performs an AWS request.

    From
    https://docs.aws.amazon.com/general/latest/gr/sigv4-signed-request-examples.html
    """
    # pylint: disable=too-many-locals
    now = datetime.datetime.utcnow()
    amz_date = now.strftime('%Y%m%dT%H%M%SZ')
    date = now.strftime('%Y%m%d')
    host = f'{service}.{region}.amazonaws.com'

    # Create the canonical request.
    canonical_uri = '/'
    canonical_querystring = urllib.parse.urlencode(
        sorted(request_parameters.items()))
    canonical_headers = f'host:{host}\nx-amz-date:{amz_date}\n'
    signed_headers = 'host;x-amz-date'
    payload_hash = hashlib.sha256(b'').hexdigest()
    canonical_request = '\n'.join([
        'GET', canonical_uri, canonical_querystring, canonical_headers,
        signed_headers, payload_hash
    ])

    # Create the string to sign.
    credential_scope = f'{date}/{region}/{service}/aws4_request'
    string_to_sign = '\n'.join([
        _ALGORITHM, amz_date, credential_scope,
        hashlib.sha256(canonical_request.encode('utf-8')).hexdigest()
    ])

    # Calculate the signature.
    signing_key = _get_signature_key(secret_key, date, region, service)
    signature = hmac.new(signing_key, string_to_sign.encode('utf-8'),
                         hashlib.sha256).hexdigest()

    # Perform the request.
    authorization_header = (
        f'{_ALGORITHM} Credential={access_key}/{credential_scope}, '
        f'SignedHeaders={signed_headers}, Signature={signature}')
    with urllib.request.urlopen(
            urllib.request.Request(f'https://{host}/?{canonical_querystring}',
                                   headers={
                                       'x-amz-date': amz_date,
                                       'Authorization': authorization_header,
                                   })) as response:
        response.read()
