#!/usr/bin/env python3
'''Manages virtual machines.'''

import argparse
import getpass
import json
import logging
import os.path
import shlex
import subprocess
import sys

from typing import Any, List, Mapping, Optional


def _run(args: List[str],
         check: bool = True) -> 'subprocess.CompletedProcess[str]':
    '''A small subprocess.run wrapper with logging.'''

    logging.debug('Running %s', ' '.join(shlex.quote(arg) for arg in args))
    result = subprocess.run(['azure'] + args,
                            stdin=subprocess.DEVNULL,
                            stdout=subprocess.PIPE,
                            universal_newlines=True,
                            shell=False,
                            check=check)
    logging.debug('Result %d: %s', result.returncode, result.stdout)
    return result


class Azure:
    '''Abstracts away the Azure CLI API.'''
    def __init__(self, subscription: str, resource_group: str, location: str):
        self._subscription = subscription
        self._resource_group = resource_group
        self._location = location

    def _nsg_name(self) -> str:
        '''Returns the network security group name.'''
        return f'{self._resource_group}-{self._location}-nsg'

    def _vnet_name(self) -> str:
        '''Returns the virtual network name.'''
        return f'{self._resource_group}-{self._location}-vnet'

    def _nic_name(self, vm_name: str) -> str:
        '''Returns the network interface card name.'''
        return f'{vm_name}-nic'

    def network_nsg_show(self) -> Optional[Mapping[str, Any]]:
        '''Returns the network security group information.'''
        result = _run([
            'network',
            'nsg',
            'show',
            '--json',
            '--subscription',
            self._subscription,
            '--resource-group',
            self._resource_group,
            '--name',
            self._nsg_name(),
        ])
        if result.returncode != 0:
            return None
        show_result: Mapping[str, Any] = json.loads(result.stdout)
        return show_result

    def network_nsg_create(self) -> Mapping[str, Any]:
        '''Creates a network security group.'''
        result: Mapping[str, Any] = json.loads(
            _run([
                'network',
                'nsg',
                'create',
                '--json',
                '--subscription',
                self._subscription,
                '--resource-group',
                self._resource_group,
                '--location',
                self._location,
                '--name',
                self._nsg_name(),
            ]).stdout)
        return result

    def network_nsg_rule_create(self, protocol: str, port: int,
                                priority: int) -> Any:
        '''Creates a network security group rule.'''
        return json.loads(
            _run([
                'network',
                'nsg',
                'rule',
                'create',
                '--json',
                '--subscription',
                self._subscription,
                '--resource-group',
                self._resource_group,
                '--nsg-name',
                self._nsg_name(),
                '--name',
                f'allow-{protocol}-port-{port}',
                '--protocol',
                protocol,
                '--destination-port-range',
                str(port),
                '--priority',
                str(priority),
            ]).stdout)

    def network_vnet_show(self) -> Optional[Mapping[str, Any]]:
        '''Returns the virtual network information.'''
        result = _run([
            'network',
            'vnet',
            'show',
            '--json',
            '--subscription',
            self._subscription,
            '--resource-group',
            self._resource_group,
            '--name',
            self._vnet_name(),
        ])
        if result.returncode != 0:
            return None
        show_result: Mapping[str, Any] = json.loads(result.stdout)
        return show_result

    def network_vnet_create(self) -> Mapping[str, Any]:
        '''Creates a virtual network.'''
        result: Mapping[str, Any] = json.loads(
            _run([
                'network',
                'vnet',
                'create',
                '--json',
                '--subscription',
                self._subscription,
                '--resource-group',
                self._resource_group,
                '--location',
                self._location,
                '--name',
                self._vnet_name(),
            ]).stdout)
        return result

    def network_vnet_subnet_create(self) -> Any:
        '''Creates a virtual network subnet.'''
        return json.loads(
            _run([
                'network',
                'vnet',
                'subnet',
                'create',
                '--json',
                '--subscription',
                self._subscription,
                '--resource-group',
                self._resource_group,
                '--vnet-name',
                self._vnet_name(),
                '--name',
                'default',
                '--address-prefix',
                '10.0.0.0/24',
            ]).stdout)

    def network_nic_show(self, vm_name: str) -> Optional[Mapping[str, Any]]:
        '''Returns the network interface card information.'''
        result: Optional[Mapping[str, Any]] = json.loads(
            _run([
                'network',
                'nic',
                'show',
                '--json',
                '--subscription',
                self._subscription,
                '--resource-group',
                self._resource_group,
                '--name',
                f'{vm_name}-nic',
            ]).stdout)
        return result

    def network_nic_create(self, vm_name: str) -> Any:
        '''Creates a network interface card.'''
        return json.loads(
            _run([
                'network', 'nic', 'create', '--json', '--subscription',
                self._subscription, '--resource-group', self._resource_group,
                '--location', self._location, '--name',
                self._nic_name(vm_name), '--subnet-vnet-name',
                self._vnet_name(), '--subnet-name', 'default',
                '--network-security-group-name',
                self._nsg_name()
            ]).stdout)

    def vm_list(self) -> List[Mapping[str, Any]]:
        '''Lists the virtual machines.'''
        result: List[Mapping[str, Any]] = json.loads(
            _run(
                ['vm', 'list', '--json', '--subscription',
                 self._subscription]).stdout)
        return result

    def vm_show(self, vm_name: str) -> Optional[Mapping[str, Any]]:
        '''Returns the virtual machine information.'''
        result: Optional[Mapping[str, Any]] = json.loads(
            _run([
                'vm', 'show', '--json', '--subscription', self._subscription,
                '--resource-group', self._resource_group, '--name', vm_name
            ]).stdout)
        return result

    # pylint: disable=too-many-arguments
    def vm_create(self,
                  vm_name: str,
                  admin_username: str,
                  ssh_publickey_file: str,
                  os_type: str = 'Linux',
                  image_urn: str = 'Canonical:UbuntuServer:16.04-LTS:latest',
                  vm_size: str = 'Standard_A1_v2') -> None:
        '''Createa a virtual machine.'''
        _run(
            [
                'vm', 'create', '--json', '--subscription', self._subscription,
                '--resource-group', self._resource_group, '--location',
                self._location, '--name', vm_name, '--admin-username',
                admin_username, '--ssh-publickey-file', ssh_publickey_file,
                '--nic-name',
                self._nic_name(vm_name), '--public-ip-name', vm_name,
                '--public-ip-domain-name', vm_name, '--os-type', os_type,
                '--image-urn', image_urn, '--vm-size', vm_size
            ],
            check=True,
        )

    def vm_destroy(self, vm_name: str) -> None:
        '''Destroys a virtual machine.'''
        _run(
            [
                'vm', 'destroy', '--json', '--subscription',
                self._subscription, '--resource-group', self._resource_group,
                '--location', self._location, '--name', vm_name
            ],
            check=True,
        )


def _deploy(azure: Azure, args: argparse.Namespace) -> None:
    deploy_runner_args = [
        os.path.join(os.path.dirname(sys.argv[0]), 'deploy_runner.py')
    ]
    if args.verbose:
        deploy_runner_args.append('--verbose')

    runner_hostname = f'{args.vm_name}.{args.location}.cloudapp.azure.com'

    vm = azure.vm_show(args.vm_name)
    if not vm:
        nsg = azure.network_nsg_show()
        if not nsg:
            nsg = azure.network_nsg_create()
        missing_ports = set(args.ports)
        for rule in nsg['securityRules']:
            missing_ports.remove(
                ':'.join((rule['protocol'].lower(),
                          rule['destinationPortRange'], rule['priority'])))
        for port in missing_ports:
            protocol, port, priority = port.split(':')
            azure.network_nsg_rule_create(protocol, int(port), int(priority))

        vnet = azure.network_vnet_show()
        if not vnet:
            vnet = azure.network_vnet_create()
        if not vnet['subnets']:
            azure.network_vnet_subnet_create()

        nic = azure.network_nic_show(args.vm_name)
        if not nic:
            azure.network_nic_create(args.vm_name)

        azure.vm_create(args.vm_name, args.username, args.pubkey_file)

        # Remove any old SSH keys associated with that hostname.
        subprocess.check_call([
            '/usr/bin/ssh-keygen', '-f',
            os.path.expanduser('~/.ssh/known_hosts'), '-R', runner_hostname
        ])

        # And accept the new SSH key.
        subprocess.check_call([
            '/usr/bin/ssh', '-o', 'StrictHostKeyChecking=no', runner_hostname,
            '/bin/true'
        ])

        deploy_runner_args.append('--upgrade')

    deploy_runner_args.extend(['--certroot', args.certroot, runner_hostname])
    subprocess.check_call(deploy_runner_args)


def _destroy(azure: Azure, args: argparse.Namespace) -> None:
    vm = azure.vm_show(args.vm_name)
    if vm:
        azure.vm_destroy(args.vm_name)


def main() -> None:
    '''Main entrypoint.'''

    parser = argparse.ArgumentParser()
    parser.add_argument('-v', '--verbose', action='store_true')
    parser.add_argument('--subscription', required=True)
    parser.add_argument('--resource-group', default='omegaup-v2-runner')
    subparsers = parser.add_subparsers(dest='command')

    deploy = subparsers.add_parser('deploy')
    deploy.add_argument('--username', default=getpass.getuser())
    deploy.add_argument('--port',
                        dest='ports',
                        metavar='PORT',
                        nargs='+',
                        default=['tcp:22:1000', 'tcp:6060:1010'])
    deploy.add_argument('--pubkey-file',
                        default=os.path.expanduser('~/.ssh/azure.pub'))
    deploy.add_argument('--certroot', required=True)
    deploy.add_argument('location')
    deploy.add_argument('vm_name', metavar='vm-name')

    destroy = subparsers.add_parser('destroy')
    destroy.add_argument('location')
    destroy.add_argument('vm_name', metavar='vm-name')

    args = parser.parse_args()

    if args.verbose:
        logging.basicConfig(level=logging.DEBUG)
    else:
        logging.basicConfig(level=logging.INFO)

    azure = Azure(args.subscription, args.resource_group, args.location)

    if args.command == 'deploy':
        _deploy(azure, args)
    elif args.command == 'destroy':
        _destroy(azure, args)


if __name__ == '__main__':
    main()

# vim: expandtab shiftwidth=4 tabstop=4
