%pragma lexer.unicode false

%skip   space                           \s+
%skip   comment_ns:space                \s+
%skip   package_ns:space                \s+
%skip   import_ns:space                 \s+
%skip   message_ns:space                \s+
%skip   interface_ns:space              \s+

%token  package_t                       package -> package_ns
%token  package_ns:package_name_t       [A-Z][a-zA-Z0-9\\]+ -> default
%token  import_t                        import -> import_ns
%token  import_ns:import_name_t         [A-Z][a-zA-Z0-9\\]+ -> default
%token  comment_start_t                 # -> comment_ns
%token  comment_ns:string_t             [\w\s]+ -> default
%token  semicolon_t                     ;
%token  message_t                       message -> message_ns
%token  message_ns:message_name_t       [A-Z][a-zA-Z0-9]+ -> default
%token  interface_t                     interface -> interface_ns
%token  interface_ns:interface_name_t   [A-Z][a-zA-Z0-9]+ -> default
%token  brace_open_t                    {
%token  brace_close_t                   }
%token  required_t                      required
%token  optional_t                      optional
%token  type_t                          [A-Z][a-zA-Z0-9]+
%token  property_name_t                 [a-z][a-zA-Z0-9]+

#root:
    ( package() | import() | comment() | message() | interface() ) *

#comment:
    ::comment_start_t:: <string_t>

#package:
    ::package_t:: <package_name_t> ::semicolon_t::

#import:
    ::import_t:: <import_name_t> ::semicolon_t::

#message:
    ::message_t:: <message_name_t> ::brace_open_t:: ( property() ) * ::brace_close_t::

#interface:
    ::interface_t:: <interface_name_t> ::brace_open_t:: ( property() ) * ::brace_close_t::

#property:
    (<required_t> | <optional_t>) <type_t> <property_name_t> ::semicolon_t::

