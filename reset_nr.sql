SELECT SETVAL('nr_category_seq','10');
SELECT SETVAL('nr_document_seq','0');
SELECT SETVAL('nr_format_seq',  '700');

DELETE FROM nr_category;
DELETE FROM nr_category_format;
DELETE FROM nr_document;
DELETE FROM nr_document_queue;
DELETE FROM nr_format;
DELETE FROM nr_htdig_status;
DELETE FROM nr_topic_category;
DELETE FROM nr_version;

INSERT INTO nr_category VALUES ('1',('Text'),       ('Any simple text: ASCII, HTML, XML, ...'),      '0');
INSERT INTO nr_category VALUES ('2',('Image'),      ('Any image type: GIF, JPG, PNG, ...'),          '0');
INSERT INTO nr_category VALUES ('3',('Audio'),      ('Any audio type: MIDI, MP3, WAV, ...'),         '0');
INSERT INTO nr_category VALUES ('4',('Video'),      ('Any video type: MPEG, Quicktime, ...'),        '0');
INSERT INTO nr_category VALUES ('5',('Model'),      ('Any 3D type: VRML'),                           '0');
INSERT INTO nr_category VALUES ('6',('Application'),('Any application type: PS, PDF, DOC, ZIP, ...'),'0');
INSERT INTO nr_category VALUES ('7',('Generic'),    ('Any document type'),                           '0');

INSERT INTO nr_category_format VALUES ('1','1');
INSERT INTO nr_category_format VALUES ('2','2');
INSERT INTO nr_category_format VALUES ('3','3');
INSERT INTO nr_category_format VALUES ('4','4');
INSERT INTO nr_category_format VALUES ('5','5');
INSERT INTO nr_category_format VALUES ('6','6');
INSERT INTO nr_category_format VALUES ('7','7');

-- any
INSERT INTO nr_format VALUES ('001',('Any Text'),               'text',       'any',                      '',    '',   'n','n');
INSERT INTO nr_format VALUES ('002',('Any Image'),              'image',      'any',                      '',    '',   'n','n');
INSERT INTO nr_format VALUES ('003',('Any Audio'),              'audio',      'any',                      '',    '',   'n','n');
INSERT INTO nr_format VALUES ('004',('Any Video'),              'video',      'any',                      '',    '',   'n','n');
INSERT INTO nr_format VALUES ('005',('Any Model'),              'model',      'any',                      '',    '',   'n','n');
INSERT INTO nr_format VALUES ('006',('Any Application'),        'application','any',                      '',    '',   'n','n');
INSERT INTO nr_format VALUES ('007',('Any Format'),             'any',        'any',                      '',    'txt',   'n','n');
-- text
INSERT INTO nr_format VALUES ('101',('HTML Text'),              'text',       'html',                     'html','txt','y','n');
INSERT INTO nr_format VALUES ('102',('Plain Text'),             'text',       'plain',                    'txt', 'txt','y','n');
INSERT INTO nr_format VALUES ('103',('SGML Text'),              'text',       'sgml',                     'sgml','txt','y','n');
INSERT INTO nr_format VALUES ('104',('WML Text'),               'text',       'vnd.wap.wml',              'wml', 'txt','y','n');
INSERT INTO nr_format VALUES ('105',('XML Text'),               'text',       'xml',                      'xml', 'txt','y','n');
-- image
INSERT INTO nr_format VALUES ('201',('GIF Image'),              'image',      'gif',                      'gif', 'img','n','n');
INSERT INTO nr_format VALUES ('202',('JPEG Image'),             'image',      'jpeg',                     'jpg', 'img','n','n');
INSERT INTO nr_format VALUES ('203',('PNG Image'),              'image',      'png',                      'png', 'img','n','n');
INSERT INTO nr_format VALUES ('204',('SVG Image'),              'image',      'svg',                      'svg', 'img','n','n');
INSERT INTO nr_format VALUES ('205',('TIFF Image'),             'image',      'tiff',                     'tif', 'img','n','n');
INSERT INTO nr_format VALUES ('206',('BMP Image'),              'image',      'x-bmp',                    'bmp', 'img','y','n');
INSERT INTO nr_format VALUES ('207',('PSD Image'),              'image',      'x-psd',	                'psd', 'img','n','n');
INSERT INTO nr_format VALUES ('208',('XBM Image'),              'image',      'x-xbitmap',                'xbm', 'img','y','n');
INSERT INTO nr_format VALUES ('209',('XCF Image'),              'image',      'x-xcf',                    'xcf', 'img','y','n');
INSERT INTO nr_format VALUES ('210',('XPM Image'),              'image',      'x-xpixmap',                'xpm', 'img','y','n');
-- audio
INSERT INTO nr_format VALUES ('301',('Sun/NeXT Audio'),         'audio',      'basic',                    'snd', 'aud','y','n');
INSERT INTO nr_format VALUES ('302',('MIDI Audio'),             'audio',      'midi',                     'mid', 'aud','y','n');
INSERT INTO nr_format VALUES ('303',('MPEG Audio'),             'audio',      'mpeg',                     'mpa', 'aud','n','n');
INSERT INTO nr_format VALUES ('304',('MP3 Audio'),              'audio',      'x-mp3',                    'mp3', 'aud','n','n');
INSERT INTO nr_format VALUES ('305',('Real Audio'),             'audio',      'x-realaudio',              'ra',  'aud','n','n');
INSERT INTO nr_format VALUES ('306',('WAV Audio'),              'audio',      'x-wav',                    'wav', 'aud','y','n');
-- video
INSERT INTO nr_format VALUES ('401',('MPEG Video'),             'video',      'mpeg',                     'mpg', 'vid','n','n');
INSERT INTO nr_format VALUES ('402',('QuickTime Video'),        'video',      'quicktime',                'mov', 'vid','n','n');
INSERT INTO nr_format VALUES ('403',('AVI Video'),              'video',      'x-msvideo',                'avi', 'vid','n','n');
INSERT INTO nr_format VALUES ('404',('ASF Video'),              'video',      'x-ms-asf',                 'asf', 'vid','n','n');
-- model
INSERT INTO nr_format VALUES ('501',('VRML Model'),             'model',      'vrml',                     'wrl', 'mod','y','n');
-- application
INSERT INTO nr_format VALUES ('601',('Word Document'),          'application','msword',                   'doc', 'app','y','y');
INSERT INTO nr_format VALUES ('602',('PDF Document'),           'application','pdf',                      'pdf', 'app','n','n');
INSERT INTO nr_format VALUES ('603',('PostScript Document'),    'application','postscript',               'ps',  'app','y','n');
INSERT INTO nr_format VALUES ('604',('RTF Document'),           'application','rtf',                      'rtf', 'app','y','n');
INSERT INTO nr_format VALUES ('605',('Excel Spreadsheet'),      'application','vnd.ms-excel',             'xls', 'app','y','y');
INSERT INTO nr_format VALUES ('606',('PowerPoint Presentation'),'application','vnd.ms-powerpoint',        'ppt', 'app','y','y');
INSERT INTO nr_format VALUES ('607',('DVI Document'),           'application','x-dvi',                    'dvi', 'app','y','n');
INSERT INTO nr_format VALUES ('608',('LaTeX Document'),         'application','x-latex',                  'tex', 'app','y','n');
INSERT INTO nr_format VALUES ('609',('OGG Document'),           'application','x-ogg',                    'ogg', 'aud','n','n');
INSERT INTO nr_format VALUES ('610',('Flash Document'),         'application','x-shockwave-flash',        'swf', 'app','n','n');
INSERT INTO nr_format VALUES ('611',('StarOffice Presentation'),'application','x-staroffice-presentation','sdd', 'app','y','n');
INSERT INTO nr_format VALUES ('612',('StarOffice Spreadsheet'), 'application','x-staroffice-spreadsheet', 'sdc', 'app','y','n');
INSERT INTO nr_format VALUES ('613',('StarOffice Document'),    'application','x-staroffice-words',       'sdw', 'app','y','n');
INSERT INTO nr_format VALUES ('614',('TAR Archive'),            'application','x-tar',                    'tar', 'cmp','y','n');
INSERT INTO nr_format VALUES ('615',('TeX Document'),           'application','x-tex',                    'tex', 'app','y','n');
INSERT INTO nr_format VALUES ('616',('WordPerfect Document'),   'application','x-wordperfect',            'wp6', 'app','y','n');
INSERT INTO nr_format VALUES ('617',('ZIP Archive'),            'application','x-zip',                    'zip', 'cmp','n','n');

INSERT INTO nr_htdig_status (running) VALUES ('n');

INSERT INTO nr_version (schema) VALUES ('1.0.2');
