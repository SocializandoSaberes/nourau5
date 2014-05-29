#
#  Makefile for NOU-RAU project
#

## Variables

DOMAIN	= nou-rau
SOURCES	= ../*.sql ../www/*.php ../www/*/*.php
LOC_DIR = ../www/locale

## Targets

default:
	@echo "Choose one target: extract, update, install or clean"

extract:
	@-mv $(DOMAIN).pot $(DOMAIN).pot.old
	@xgettext -C -k_ -k_M -o$(DOMAIN).pot -s $(SOURCES)

update:
	@for po in *.po; do \
	  mv $$po $$po.old; \
	  msgmerge -o$$po -v $$po.old $(DOMAIN).pot; \
	done

install:
	@for po in *.po; do \
          lang=`echo $$po | cut -d. -f1 -`; \
	  mkdir -p $(LOC_DIR)/$$lang/LC_MESSAGES; \
	  msgfmt -o $(LOC_DIR)/$$lang/LC_MESSAGES/$(DOMAIN).mo $$po; \
	done

clean:
	@-rm -f *~ *.old

.PHONY: default extract update install clean
