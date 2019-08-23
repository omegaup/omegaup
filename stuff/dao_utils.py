# -*- coding: utf-8 -*-
'''A tool that helps update the DAOs.'''

from __future__ import print_function

import datetime
import os
from typing import NamedTuple, Text, Sequence

import jinja2
from pyparsing import (CaselessKeyword, Optional, ParseException, Regex,
                       Suppress, Word, ZeroOrMore, alphanums, delimitedList)


# pylint: disable=too-many-instance-attributes
class Column:
    '''Represents a MySQL column definition.'''

    def __init__(self, tokens):
        self.name = tokens['col_name']
        self.type = tuple(tokens['col_type'])
        self.primary_key = False
        self.auto_increment = (tokens.get('auto_increment',
                                          '').upper() == 'AUTO_INCREMENT')
        self.not_null = ('nullability' in tokens
                         and tokens['nullability'].upper() == 'NOT NULL')
        self.default = tokens.get('default', None)
        if self.default == 'NULL':
            self.default = None
        self.comment = tokens.get('comment', None)
        if 'tinyint' in self.type:
            self.php_primitive_type = 'bool'
        elif 'timestamp' in self.type or 'datetime' in self.type:
            self.php_primitive_type = 'int'
        elif 'int' in self.type:
            self.php_primitive_type = 'int'
        elif 'double' in self.type:
            self.php_primitive_type = 'float'
        else:
            self.php_primitive_type = 'string'
        self.php_type = (
            ('' if self.default or self.auto_increment else '?') +
            self.php_primitive_type)

    def __repr__(self):
        return 'Column<name={}, type={}>'.format(self.name, self.type)


class Constraint:
    '''Represents a MySQL column constraint.'''

    def __init__(self, tokens):
        self.type = tokens['type']
        self.columns = tokens['key_part']

    def __repr__(self):
        return 'Constraint<type={}, columns={}>'.format(
            self.type, self.columns)


class Table:
    '''Represents a MySQL table.'''

    def __init__(self, tokens):
        self.name = tokens['tbl_name']
        self.class_name = tokens['tbl_name'].replace('_', '')
        self.columns = tokens['column']
        self.constraints = tokens.get('constraint', [])
        for constraint in self.constraints:
            if constraint.type == 'PRIMARY KEY':
                primary_key_columns = set(
                    column_name for column_name in constraint.columns)
                for column in self.columns:
                    if column.name not in primary_key_columns:
                        continue
                    column.primary_key = True

    @property
    def fieldnames(self):
        '''A quoted, comma-separated list of fields.'''

        return ', '.join("`{}`.`{}`".format(self.name, column.name)
                         for column in self.columns)

    def __repr__(self):
        return 'Table<name={}, columns={}>'.format(self.name, self.columns)


def _parse(text: Text):
    comment = Suppress('/*' + Regex(r'([^*]|[*][^/])*') + '*/')

    identifier = (Suppress('`') + Regex(r'[^`]+') +
                  Suppress('`')).setParseAction(lambda toks: toks[0])

    string = (Suppress("'") + Regex(r"([^']|\\.)*") +
              Suppress("'")).setParseAction(lambda toks: toks[0])

    reference_option = (CaselessKeyword('RESTRICT')
                        | CaselessKeyword('CASCADE')
                        | CaselessKeyword('SET NULL')
                        | CaselessKeyword('NO ACTION')
                        | CaselessKeyword('SET DEFAULT'))

    reference_definition = (
        Suppress(
            CaselessKeyword('REFERENCES')) + identifier('reference_tbl_name') +
        '(' + delimitedList(identifier)('tbl_column') + ')' +
        ZeroOrMore((Suppress(CaselessKeyword('ON DELETE')) +
                    reference_option('on_delete'))
                   | (Suppress(CaselessKeyword('ON UPDATE')) +
                      reference_option('on_update'))))

    constraint_definition = (
        (((CaselessKeyword('PRIMARY KEY')('type')) |
          ((CaselessKeyword('FULLTEXT KEY') | CaselessKeyword('UNIQUE KEY')
            | CaselessKeyword('KEY'))('type') + identifier('index_name'))) +
         '(' + delimitedList(identifier('key_part*')) + ')') |
        (Suppress(CaselessKeyword('CONSTRAINT')) + identifier('symbol') +
         (CaselessKeyword('FOREIGN KEY')('type') + '(' + delimitedList(
             identifier('key_part*')) + ')' + reference_definition))
    ).setParseAction(Constraint)

    column_type = (Word(alphanums) + Optional('(' + Regex('[^)]+') + ')') +
                   Optional(Suppress(CaselessKeyword('UNSIGNED'))))

    column_definition = (
        identifier('col_name') + column_type('col_type') + ZeroOrMore(
            (CaselessKeyword('NULL')
             | CaselessKeyword('NOT NULL'))('nullability')
            | (CaselessKeyword('AUTO_INCREMENT'))('auto_increment')
            | (Suppress(CaselessKeyword('COMMENT')) + string('comment'))
            | (Suppress(CaselessKeyword('DEFAULT')) +
               (Word(alphanums + '_')
                | string).setParseAction(lambda toks: toks[0])('default'))
            | (Suppress(CaselessKeyword('ON DELETE')) +
               (Word(alphanums + '_') | reference_option)('on_delete'))
            | (Suppress(CaselessKeyword('ON UPDATE')) +
               (Word(alphanums + '_') | reference_option)('on_update')))
    ).setParseAction(Column)

    create_definition = column_definition('column*') | constraint_definition(
        'constraint*')

    create_table_statement = (
        Suppress(CaselessKeyword('CREATE') + CaselessKeyword('TABLE')) +
        identifier('tbl_name') + Suppress('(') +
        delimitedList(create_definition) + Suppress(')') + Suppress(
            Regex('[^;]*'))).setParseAction(Table)

    parser = delimitedList(
        comment | create_table_statement('table*'), delim=';') + Suppress(
            Optional(';'))

    return parser.parseString(text, parseAll=True)['table']


# pylint: disable=redefined-builtin
def _listformat(value, format: Text = '', **kwargs):
    return [format.format(element, **kwargs) for element in value]


def _parse_date(value):
    return int(datetime.datetime.strptime(
        value,
        '%Y-%m-%d %H:%M:%S').replace(tzinfo=datetime.timezone.utc).timestamp())


DaoFile = NamedTuple('DaoFile', [('filename', Text), ('contents', Text)])


def generate_dao(script: Text) -> Sequence[DaoFile]:
    '''Generate all the DAO files.'''

    try:
        tables = _parse(script)
    except ParseException as ex:
        print('Parse error: {}'.format(ex))
        print('{:5d}: {}'.format(ex.lineno, ex.line))
        print('{}^'.format(' ' * (ex.col + 6)))
        raise ex
    env = jinja2.Environment(
        loader=jinja2.FileSystemLoader(
            os.path.join(
                os.path.dirname(os.path.abspath(__file__)), 'dao_templates')))
    env.filters['listformat'] = _listformat
    env.filters['strtotime'] = _parse_date
    vo_template = env.get_template('vo.php')
    dao_template = env.get_template('dao.php')
    for table in tables:
        yield DaoFile('{}.php'.format(table.class_name),
                      vo_template.render(table=table))
        yield DaoFile('{}.dao.base.php'.format(table.name),
                      dao_template.render(table=table))


# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
