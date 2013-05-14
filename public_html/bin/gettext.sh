echo '' > includes/locale/messages.po

find tmp/cache/ -type f -iname "*.php"  | xgettext --keyword=__ --output-dir includes/locale/ --output messages.po --keyword=_e -j -f -


msgfmt includes/locale/messages.po -o includes/locale/messages.mo