#!/usr/bin/perl

# Convert the document of the given MIME type to HTML

use strict;
use File::Copy;
use POSIX;

my $XCAT   = $ENV{'XCAT'};
my $XGZIP  = $ENV{'XGZIP'};
my $XTOUCH = $ENV{'XTOUCH'};

($XCAT && $XGZIP && $XTOUCH) || die "convert: environment must be set\n";

my $XDOC = $ENV{'XDOC'};
my $XDVI = $ENV{'XDVI'};
my $XPDF = $ENV{'XPDF'};
my $XPPT = $ENV{'XPPT'};
my $XPS  = $ENV{'XPS'};
my $XTEX = $ENV{'XTEX'};
my $XXLS = $ENV{'XXLS'};

($#ARGV >= 2) || die "syntax: convert <type> <infile> <outfile>\n";

my $type = shift();
my $infile = shift();
my $outfile = shift();
my $temp;

# ---------------- open output ---------------- #
open(OUT, ">$outfile") || die "convert:cannot open output file '$outfile'\n";
print(OUT "<html><head>\n");
if ($ENV{'TITLE'}) {
    print(OUT '<title>', $ENV{'TITLE'}, "</title>\n");
}
if ($ENV{'KEYWORDS'}) {
    print(OUT '<meta name="keywords" content="', $ENV{'KEYWORDS'}, "\">\n");
}
if ($ENV{'DESCRIPTION'}) {
    print(OUT '<meta name="description" content="', $ENV{'DESCRIPTION'},
	  "\">\n");
}
print(OUT "</head><body>\n");
print(OUT $ENV{'DESCRIPTION'}, "\n");
print(OUT $ENV{'KEYWORDS'}, "\n");

# ---------------- read and process input ---------------- #
if ($type ne 'none') {
    my $compressed = ($infile =~ /\.gz$/);
    my $mode;
    my $tool;

    # ---------------- select conversion tool ---------------- #
    if ($type eq 'text/html' || $type eq 'text/plain' ||
	$type eq 'text/sgml' || $type eq 'text/vnd.wap.wml' ||
	$type eq 'text/xml' || $type eq 'application/rtf') {
	$mode = ($compressed) ? "$XGZIP -cd $infile |" : "<$infile";
    }
    elsif ($type eq 'application/msword') {
	$compressed && make_temp();
	$mode = "$XDOC $infile |";
	$tool = $XDOC;
    }
    elsif ($type eq 'application/pdf') {
	$compressed && make_temp();
	$mode = "$XPDF $infile - |";
	$tool = $XPDF;
    }
    elsif ($type eq 'application/postscript') {
	$mode = ($compressed) ? "$XGZIP -cd $infile | $XPS |" :
	    "$XPS $infile |";
	$tool = $XPS;
    }
    elsif ($type eq 'application/vnd.ms-excel') {
	$compressed && make_temp();
	$mode = "$XXLS -asc -xp:0 $infile |";
	$tool = $XXLS;
    }
    elsif ($type eq 'application/vnd.ms-powerpoint') {
	$compressed && make_temp();
	$mode = "$XPPT $infile |";
	$tool = $XPPT;
    }
    elsif ($type eq 'application/x-dvi') {
	$compressed && make_temp();
	$mode = "$XDVI -q -w132 $infile |";
	$tool = $XDVI;
    }
    elsif (($type eq 'application/x-latex' || $type eq 'application/x-tex')) {
	$mode = ($compressed) ? "$XGZIP -cd $infile |" : "$XCAT $infile |";
	$mode .= "$XTEX tex..latin1 |";
	$tool = $XTEX;
    }

    if (!-r $infile) {
	print "convert:cannot open input file '$infile'\n";
	undef $mode;
    }
    if ($tool && !-x $tool) {
	print "convert: cannot find tool '$tool'\n";
	undef $mode;
    }

    # ---------------- read data ---------------- #
    if ($mode) {
	my $data;
	undef $/;

	if (open(IN, $mode)) {
	    $data = <IN>;
	}
	else {
	    print "convert:cannot open input file '$infile'\n";
	}

	# ---------------- process data ---------------- #
	if ($type eq 'application/rtf') {
	    $data =~ s/\\\'([0-9a-f]{2})/pack('H2',$1)/egi; # decode chars
	    $data =~ s/\\\w+//g; # remove RTF markup
	}

	if ($type eq 'text/sgml' || $type eq 'text/vnd.wap.wml' ||
	    $type eq 'text/xml') {
	    $data =~ s/<[^>]+>/ /g; # replace tags by spaces
	}

	if ($type eq 'application/x-dvi') {
	    $data =~ s/\*\n \*//g; # unfold long lines
	}

	if ($type eq 'application/x-latex' || $type eq 'application/x-tex') {
	    $data =~ s/\\\w+//g; # remove TeX commands
	    $data =~ s/[{}]/ /g; # replace braces by spaces
	}

	if ($type eq 'text/html' || $type eq 'application/vnd.ms-powerpoint') {
	    # wipe any HTML outside the document body
	    $data =~ s|^.*<body[^>]*>||is;
	    $data =~ s|</body>.*$||is;
	}
	else {
	    # convert plain text to HTML
	    $data =~ s/\f/\n/g;
	    $data =~ s/&/\&amp\;/g;
	    $data =~ s/</\&lt\;/g;
	    $data =~ s/>/\&gt\;/g;
	}

	# ---------------- write data ---------------- #
	print(OUT $data);

	# ---------------- finish input ---------------- #
	close(IN);
	if ($temp) {
	    unlink($temp);
	}
    }
}

# ---------------- finish output ---------------- #
print(OUT "</body></html>\n");
close(OUT);
if ($ENV{'MODIFIED'}) {
    system("$XTOUCH -d '" . $ENV{'MODIFIED'} . "' $outfile");
}

sub make_temp {
    $temp = tmpnam();
    copy($infile, "$temp.gz");
    system("$XGZIP -d $temp.gz");
    $infile = $temp;
}
