# -*- coding: utf-8 -*-
'''A tool that helps update the DAOs.'''

from __future__ import print_function

import datetime
import os
from typing import (Any, Generator, List, Mapping, NamedTuple, Optional,
                    Sequence)

import jinja2
from pyparsing import (  # type: ignore
    CaselessKeyword, Optional as Opt, ParseException, ParseResults, Regex,
    Suppress, Word, ZeroOrMore, alphanums, delimited_list)


# pylint: disable=too-many-instance-attributes,too-few-public-methods
class Column:
    '''Represents a MySQL column definition.'''
    def __init__(self, tokens: Mapping[str, Any]):
        self.name: str = tokens['col_name']
        self.type: Sequence[str] = tuple(tokens['col_type'])
        self.primary_key: bool = False
        self.auto_increment: bool = (tokens.get(
            'auto_increment', '').upper() == 'AUTO_INCREMENT')
        self.not_null: bool = ('nullability' in tokens
                               and tokens['nullability'].upper() == 'NOT NULL')
        self.default: Optional[str] = tokens.get('default', None)
        if self.default == 'NULL':
            self.default = None
        self.comment: Optional[str] = tokens.get('comment', None)
        if 'tinyint' in self.type:
            self.php_primitive_type = 'bool'
        elif 'timestamp' in self.type or 'datetime' in self.type:
            self.php_primitive_type = '\\OmegaUp\\Timestamp'
        elif 'int' in self.type:
            self.php_primitive_type = 'int'
        elif 'double' in self.type:
            self.php_primitive_type = 'float'
        else:
            self.php_primitive_type = 'string'
        self.php_type: str = (
            ('' if self.default or self.auto_increment else '?') +
            self.php_primitive_type)

    def __repr__(self) -> str:
        return f'Column<name={self.name}, type={self.type}>'


class Constraint:
    '''Represents a MySQL column constraint.'''
    def __init__(self, tokens: ParseResults):
        self.type: str = tokens.type
        self.columns: Sequence[str] = tokens.get('key_part', ())

    def __repr__(self) -> str:
        return f'Constraint<type={self.type}, columns={self.columns}>'


class Table:
    '''Represents a MySQL table.'''
    def __init__(self, tokens: ParseResults):
        self.name: str = tokens.tbl_name
        self.class_name: str = tokens.tbl_name.replace('_', '')
        self.columns: Sequence[Column] = [column for column in tokens
                                          if isinstance(column, Column)]
        self.constraints: Sequence[Constraint] = [
            constraint for constraint in tokens
            if isinstance(constraint, Constraint)]
        for constraint in self.constraints:
            if constraint.type == 'PRIMARY KEY':
                primary_key_columns = set(
                    column_name for column_name in constraint.columns)
                for column in self.columns:
                    if column.name not in primary_key_columns:
                        continue
                    column.primary_key = True

    @property
    def fieldnames(self) -> List[str]:
        '''A quoted list of fields.'''

        return [
            f"`{self.name}`.`{column.name}`"
            for column in self.columns
        ]

    def __repr__(self) -> str:
        return f'Table<name={self.name}, columns={self.columns}>'


def _parse(text: str) -> Sequence[Table]:
    comment = Suppress('/*' + Regex(r'([^*]|[*][^/])*') + '*/')

    identifier = (Suppress('`') + Regex(r'[^`]+') +
                  Suppress('`')).set_parse_action(lambda toks: toks[0])

    string = (Suppress("'") + Regex(r"([^']|\\.)*") +
              Suppress("'")).set_parse_action(lambda toks: toks[0])

    reference_option = (CaselessKeyword('RESTRICT')
                        | CaselessKeyword('CASCADE')
                        | CaselessKeyword('SET NULL')
                        | CaselessKeyword('NO ACTION')
                        | CaselessKeyword('SET DEFAULT'))

    reference_definition = (
        Suppress(CaselessKeyword('REFERENCES')) +
        identifier('reference_tbl_name') + '(' +
        delimited_list(identifier)('tbl_column') + ')' +
        ZeroOrMore((Suppress(CaselessKeyword('ON DELETE')) +
                    reference_option('on_delete'))
                   | (Suppress(CaselessKeyword('ON UPDATE')) +
                      reference_option('on_update'))))

    # Support for ASC/DESC ordering in index definitions
    index_column = (
        identifier +
        Opt(Suppress(CaselessKeyword('ASC') | CaselessKeyword('DESC')))
    ).set_parse_action(lambda toks: toks[0])

    constraint_definition = (
        (((CaselessKeyword('PRIMARY KEY')('type')) |
          ((CaselessKeyword('FULLTEXT KEY') | CaselessKeyword('UNIQUE KEY')
            | CaselessKeyword('KEY'))('type') + identifier('index_name'))) +
         '(' + delimited_list(index_column)('key_part') + ')') |
        (Suppress(CaselessKeyword('CONSTRAINT')) + identifier('symbol') +
         ((CaselessKeyword('FOREIGN KEY')
           ('type') + '(' + delimited_list(identifier)
           ('key_part') + ')' + reference_definition)
          | (CaselessKeyword('CHECK')('type') + Regex('[^,\n]+'))))
    ).set_parse_action(Constraint)

    column_type = (Word(alphanums) + Opt('(' + Regex('[^)]+') + ')') +
                   Opt(Suppress(CaselessKeyword('UNSIGNED'))))

    column_definition = (
        identifier('col_name') + column_type('col_type') + ZeroOrMore(
            (CaselessKeyword('NULL')
             | CaselessKeyword('NOT NULL'))('nullability')
            | (CaselessKeyword('AUTO_INCREMENT'))('auto_increment')
            | (Suppress(CaselessKeyword('COMMENT')) + string('comment'))
            | (Suppress(CaselessKeyword('DEFAULT')) +
               (Word(alphanums + '_')
                | string).set_parse_action(lambda toks: toks[0])('default'))
            | (Suppress(CaselessKeyword('ON DELETE')) +
               (Word(alphanums + '_') | reference_option)('on_delete'))
            | (Suppress(CaselessKeyword('ON UPDATE')) +
               (Word(alphanums + '_') | reference_option)('on_update')))
    ).set_parse_action(Column)

    create_definition = (column_definition('column*')
                         | constraint_definition('constraint*'))

    create_table_statement = (
        Suppress(CaselessKeyword('CREATE') + CaselessKeyword('TABLE')) +
        identifier('tbl_name') + Suppress('(') +
        delimited_list(create_definition) + Suppress(')') +
        Suppress(Regex('[^;]*'))).set_parse_action(Table)

    parser = delimited_list(comment | create_table_statement('table*'),
                            delim=';') + Suppress(Opt(';'))

    table: Sequence[Table] = parser.parseString(text, parseAll=True)['table']
    return table


# pylint: disable=redefined-builtin
def _listformat(value: Any, format: str = '', **kwargs: Any) -> Sequence[str]:
    return [format.format(element, **kwargs) for element in value]


def _parse_date(value: str) -> int:
    return int(
        datetime.datetime.strptime(value, '%Y-%m-%d %H:%M:%S').replace(
            tzinfo=datetime.timezone.utc).timestamp())


File = NamedTuple('File', [('filename', str), ('file_type', str),
                           ('contents', str)])


def generate_dao(script: str) -> Generator[File, None, None]:
    '''Generate all the DAO files.'''

    try:
        tables = _parse(script)
    except ParseException as ex:
        print(f'Parse error: {ex}')
        print(f'{ex.lineno:5d}: {ex.line}')
        print(f'{" " * (ex.col + 6)}^')
        raise ex
    env = jinja2.Environment(loader=jinja2.FileSystemLoader(
        os.path.join(os.path.dirname(os.path.abspath(__file__)),
                     'dao_templates')))
    env.filters['listformat'] = _listformat
    env.filters['strtotime'] = _parse_date
    vo_template = env.get_template('vo.php')
    dao_template = env.get_template('dao.php')
    for table in tables:
        yield File(f'{table.class_name}.php', 'vo',
                   vo_template.render(table=table))
        yield File(f'{table.class_name}.php', 'dao',
                   dao_template.render(table=table))


# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
