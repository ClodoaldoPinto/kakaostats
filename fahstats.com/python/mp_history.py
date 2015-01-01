#!/usr/bin/env python

import history
from convertArray import convertArray

def index(req):

    _donor = int(req.form.getfirst('d'))
    _tables = req.form.getfirst('t').split(';')

    _l = list()

    for _t in _tables:
        _l.append(history.table_list(_donor, _t))

    return convertArray(_l)
