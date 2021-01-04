#! /usr/bin/env perl
# Copyright 2015-2020 The OpenSSL Project Authors. All Rights Reserved.
#
# Licensed under the Apache License 2.0 (the "License").  You may not use
# this file except in compliance with the License.  You can obtain a copy
# in the file LICENSE in the source distribution or at
# https://www.openssl.org/source/license.html


use strict;
use warnings;

use File::Spec;
use OpenSSL::Test qw/:DEFAULT srctop_file/;
use OpenSSL::Test::Utils;

setup("test_ec");

plan tests => 14;

require_ok(srctop_file('test','recipes','tconversion.pl'));

ok(run(test(["ectest"])), "running ectest");

# TODO: remove these when the 'ec' app is removed.
# Also consider moving this to the 20-25 test section because it is testing
# the command line tool in addition to the algorithm.
SKIP: {
    skip "Skipping EC conversion test", 3
        if disabled("ec");

    subtest 'EC conversions -- private key' => sub {
        tconversion( -type => 'ec', -prefix => 'ec-priv',
                     -in => srctop_file("test","testec-p256.pem") );
    };
    subtest 'EC conversions -- private key PKCS#8' => sub {
        tconversion( -type => 'ec', -prefix => 'ec-pkcs8',
                     -in => srctop_file("test","testec-p256.pem"),
                     -args => "pkey" );
    };
    subtest 'EC conversions -- public key' => sub {
        tconversion( -type => 'ec', -prefix => 'ec-pub',
                     -in => srctop_file("test","testecpub-p256.pem"),
                     -args => [ "ec", "-pubin", "-pubout" ] );
    };
}

SKIP: {
    skip "Skipping PKEY conversion test", 3
        if disabled("ec");

    subtest 'PKEY conversions -- private key' => sub {
        tconversion( -type => 'pkey', -prefix => 'ec-pkey-priv',
                     -in => srctop_file("test","testec-p256.pem") );
    };
    subtest 'PKEY conversions -- private key PKCS#8' => sub {
        tconversion( -type => 'pkey', -prefix => 'ec-pkey-pkcs8',
                     -in => srctop_file("test","testec-p256.pem"),
                     -args => "pkey" );
    };
    subtest 'PKEY conversions -- public key' => sub {
        tconversion( -type => 'pkey', -prefix => 'ec-pkey-pub',
                     -in => srctop_file("test","testecpub-p256.pem"),
                     -args => [ "pkey", "-pubin", "-pubout" ] );
    };
}

SKIP: {
    skip "Skipping EdDSA conversion test", 6
        if disabled("ec");

    subtest 'Ed25519 conversions -- private key' => sub {
        tconversion( -type => "pkey", -prefix => "ed25519-pkey-priv",
                     -in => srctop_file("test", "tested25519.pem") );
    };
    subtest 'Ed25519 conversions -- private key PKCS#8' => sub {
        tconversion( -type => "pkey", -prefix => "ed25519-pkey-pkcs8",
                     -in => srctop_file("test", "tested25519.pem"),
                     -args => ["pkey"] );
    };
    subtest 'Ed25519 conversions -- public key' => sub {
        tconversion( -type => "pkey", -prefix => "ed25519-pkey-pub",
                     -in => srctop_file("test", "tested25519pub.pem"),
                     -args => ["pkey", "-pubin", "-pubout"] );
    };

    subtest 'Ed448 conversions -- private key' => sub {
        tconversion( -type => "pkey", -prefix => "ed448-pkey-priv",
                     -in => srctop_file("test", "tested448.pem") );
    };
    subtest 'Ed448 conversions -- private key PKCS#8' => sub {
        tconversion( -type => "pkey", -prefix => "ed448-pkey-pkcs8",
                     -in => srctop_file("test", "tested448.pem"),
                     -args => ["pkey"] );
    };
    subtest 'Ed448 conversions -- public key' => sub {
        tconversion( -type => "pkey", -prefix => "ed448-pkey-pub",
                     -in => srctop_file("test", "tested448pub.pem"),
                     -args => ["pkey", "-pubin", "-pubout"] );
    };
}
