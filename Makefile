# 
#  Makefile for NOU-RAU project
#

## Configuration

# name of POSTGRESQL database
BASE = nourau

# name of POSTGRESQL user
USER = rafael

# language of choice
LANG = pt_BR

# where to install the Nou-Rau data files
NRDIR = /opt/nourau

# where the HTDIG package was installed
HTDIG = /usr/bin/htdig

# where to find various tools (add complete path if needed)
DUMP = pg_dump
PSQL = psql
PHP  = php


## You do not need to change anything below

default:
	@echo "Choose one target: install (install or upgrade database),"
	@echo "                   backup  (backup current database)"

install: backup make_dir
	 export BASE=$(BASE); \
 	 #export BASE=bib; \
	 export USER=$(USER); \
	 export PSQL=$(PSQL); \
	 export LANG=$(LANG); \
	 $(PHP) -f install.php

backup: check
	@stamp=`date +%Y%m%d-%H%M%S`; \
	 $(DUMP) $(BASE) -U $(USER) -h localhost> $(BASE)-$$stamp.sql

check:
	@if ! which $(DUMP) > /dev/null 2>&1; then \
	 echo "error: tool '$(DUMP)' not found"; exit 1; fi
	@if ! which $(PSQL) > /dev/null 2>&1; then \
	 echo "error: tool '$(PSQL)' not found"; exit 1; fi
	@if ! which $(PHP)  > /dev/null 2>&1; then \
	 echo "error: tool '$(PHP)' not found"; exit 1; fi
	@if ! test -d $(HTDIR); then \
	 echo "error: package 'HTDIG' not installed in '$(HTDIG)'"; exit 1; fi

make_dir:
	@if ! test -d $(NRDIR); then \
	 echo "Entrou"; exit 1; fi
	 mkdir -p $(NRDIR);          chmod 0755 $(NRDIR); \
	 mkdir -p $(NRDIR)/archive;  chmod 1777 $(NRDIR)/archive; \
	 mkdir -p $(NRDIR)/htdig;    chmod 0777 $(NRDIR)/htdig; \
	 mkdir -p $(NRDIR)/incoming; chmod 1777 $(NRDIR)/incoming; \
	 mkdir -p $(NRDIR)/share;    chmod 0755 $(NRDIR)/share; \
	 mkdir -p $(NRDIR)/temp;     chmod 1777 $(NRDIR)/temp; \
	 cp -a share/convert.pl share/mime.magic share/*html $(NRDIR)/share; \
	 cp -a share/nourau.conf $(NRDIR); \
	 cp -a share/nourau.conf $(HTDIR)/conf; fi

clean:
	@-rm -f *~ */*~ */*/*~ */*/*/*~
	@$(MAKE) -C po -s clean

.PHONY: default install backup check make_dir clean
