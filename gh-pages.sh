#!/bin/bash

composer doc

rm -rf ../phramework-doc
mkdir ../phramework-doc

git clone -b gh-pages git@github.com:phramework/phramework.git ../phramework-doc
(cd ../phramework-doc && git rm -r .)
cp -r doc/* ../phramework-doc
(cd ../phramework-doc && git add .)
(cd ../phramework-doc && git commit -a -m 'Update doc')
(cd ../phramework-doc && git push origin gh-pages)
rm -rf ../phramework-doc
