#!/bin/bash

PATH_FROM_ROOT="./download-all/animejpnsub"

# Comment out lines below to prevent categories from being downloaded

$PATH_FROM_ROOT/download-tv.sh # 71 items (anime)
$PATH_FROM_ROOT/download-movie.sh # 46 items
$PATH_FROM_ROOT/download-drama.sh # 4 items
$PATH_FROM_ROOT/download-shorts.sh # 3 items (short anime series)
$PATH_FROM_ROOT/download-short.sh # 2 items (short movies)
$PATH_FROM_ROOT/download-ova.sh # 1 items (anime that wasn't aired on TV)

# 124 items in total as of 2019-07-02
