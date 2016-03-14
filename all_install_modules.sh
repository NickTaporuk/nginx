#!/usr/bin/env bash
2>&1 nginx -V | tr -- - '\n' | grep _module