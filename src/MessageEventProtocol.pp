%pragma lexer.unicode false

%skip   space                           \s+
%skip   comment_ns:space                \s+
%skip   package_ns:space                \s+
%skip   import_ns:space                 \s+
%skip   message_ns:space                \s+
%skip   interface_ns:space              \s+
%skip   implements_ns:space             \s+
%skip   option_ns:space                 \s+
%skip   annotation_ns:space             \s+
%skip   extend_ns:space                 \s+

%token  required_t                      required
%token  optional_t                      optional
%token  option_t                        option -> option_ns
%token  option_ns:option_name_t         [a-z][a-z0-9_]+ -> default
%token  implements_t                    implements -> implements_ns
%token  implements_ns:implements_name_t [A-Z][a-zA-Z0-9]+ -> default
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
%token  extend_t                        extends -> extend_ns
%token  extend_ns:extend_name_t         [A-Z][a-zA-Z0-9]+ -> default
%token  brace_open_t                    {
%token  brace_close_t                   }
%token  type_t                          [A-Z][a-zA-Z0-9]+
%token  property_name_t                 [a-z][a-zA-Z0-9]+
%token  equal_t                         =
%token  quote_start_t                   " -> string_ns
%token  string_ns:string_t              [^"]+
%token  string_ns:quote_end_t           " -> default
%token  annotation_t                    @ -> annotation_ns
%token  annotation_ns:annotation_name_t [A-Z][a-zA-Z0-9]+ -> default
%token  bracket_open_t                  \(
%token  bracket_close_t                 \)


#root:
    ( package() | options() | import() | comment() | message() | interface() ) *

#options:
    ::option_t:: <option_name_t> ::equal_t:: ::quote_start_t:: <string_t> ::quote_end_t:: ::semicolon_t::

#comment:
    ::comment_start_t:: <string_t>

#package:
    ::package_t:: <package_name_t> ::semicolon_t::

#import:
    ::import_t:: <import_name_t> ::semicolon_t::

#implements:
    ::implements_t:: <implements_name_t>

#extend:
    ::extend_t:: <extend_name_t>

#message:
    ::message_t:: <message_name_t> ( extend() ) ? ( implements() ) ? ::brace_open_t:: ( property() ) * ::brace_close_t::

#interface:
    ::interface_t:: <interface_name_t> ::brace_open_t:: ( property() ) * ::brace_close_t::

#property:
   ( annotation() ) * ( <required_t> | <optional_t> ) <type_t> <property_name_t> ::semicolon_t::

#annotation:
    ::annotation_t:: ::annotation_name_t:: ::bracket_open_t:: ( ::quote_start_t:: <string_t> ::quote_end_t:: ) ? ::bracket_close_t::
