<?php

// Function that checks whether the data are the on-screen text.
// It works in the following way:
// an array arrfailAt stores the control words for the current state of the stack,
// which show that input data are something else than plain text.
// For example, there may be a description of font or color palette etc.
function rtf_isPlainText($s) {
    $arrfailAt = array("*", "fonttbl", "colortbl", "datastore", "themedata");
    for ($i = 0; $i < count($arrfailAt); $i++)
        if (!empty($s[$arrfailAt[$i]])) return false;
    return true;
}

function rtf2text($stringrtf) {
    // Read the data from the input file.
    $text = $stringrtf;

    // Create empty stack array.
    $document = "";
    $stack = array();
    $j = -1;
    // Read the data character-by- character…
    for ($i = 0, $len = strlen($text); $i < $len; $i++) {
        $c = $text[$i];

        // Depending on current character select the further actions.
        switch ($c) {
            // the most important key word backslash
            case "\\":
                // read next character
                $nc = $text[$i + 1];

                // If it is another backslash or nonbreaking space or hyphen,
                // then the character is plain text and add it to the output stream.
                if ($nc == '\\' && rtf_isPlainText($stack[$j])) $document .= '\\';
                elseif ($nc == '~' && rtf_isPlainText($stack[$j])) $document .= ' ';
                elseif ($nc == '_' && rtf_isPlainText($stack[$j])) $document .= '-';
                // If it is an asterisk mark, add it to the stack.
                elseif ($nc == '*') $stack[$j]["*"] = true;
                // If it is a single quote, read next two characters that are the hexadecimal notation
                // of a character we should add to the output stream.
                elseif ($nc == "'") {
                    $hex = substr($text, $i + 2, 2);
                    if (rtf_isPlainText($stack[$j]))
                        $document .= html_entity_decode("&#".hexdec($hex).";");
                    //Shift the pointer.
                    $i += 2;
                // Since, we’ve found the alphabetic character, the next characters are control word
                // and, possibly, some digit parameter.
                } elseif ($nc >= 'a' && $nc <= 'z' || $nc >= 'A' && $nc <= 'Z') {
                    $word = "";
                    $param = null;

                    // Start reading characters after the backslash.
                    for ($k = $i + 1, $m = 0; $k < strlen($text); $k++, $m++) {
                        $nc = $text[$k];
                        // If the current character is a letter and there were no digits before it,
                        // then we’re still reading the control word. If there were digits, we should stop
                        // since we reach the end of the control word.
                        if ($nc >= 'a' && $nc <= 'z' || $nc >= 'A' && $nc <= 'Z') {
                            if (empty($param))
                                $word .= $nc;
                            else
                                break;
                        // If it is a digit, store the parameter.
                        } elseif ($nc >= '0' && $nc <= '9')
                            $param .= $nc;
                        // Since minus sign may occur only before a digit parameter, check whether
                        // $param is empty. Otherwise, we reach the end of the control word.
                        elseif ($nc == '-') {
                            if (empty($param))
                                $param .= $nc;
                            else
                                break;
                        } else
                            break;
                    }
                    // Shift the pointer on the number of read characters.
                    $i += $m - 1;

                    // Start analyzing what we’ve read. We are interested mostly in control words.
                    $toText = "";
                    switch (strtolower($word)) {
                        // If the control word is "u", then its parameter is the decimal notation of the
                        // Unicode character that should be added to the output stream.
                        // We need to check whether the stack contains \ucN control word. If it does,
                        // we should remove the N characters from the output stream.
                        case "u":
                            $toText .= html_entity_decode("&#x".dechex($param).";");
                            $ucDelta = @$stack[$j]["uc"];
                            if ($ucDelta > 0)
                                $i += $ucDelta;
                        break;
                        // Select line feeds, spaces and tabs.
                        case "par": case "page": case "column": case "line": case "lbr":
                            $toText .= "\n";
                        break;
                        case "emspace": case "enspace": case "qmspace":
                            $toText .= " ";
                        break;
                        case "tab": $toText .= "\t"; break;
                        // Add current date and time instead of corresponding labels.
                        case "chdate": $toText .= date("m.d.Y"); break;
                        case "chdpl": $toText .= date("l, j F Y"); break;
                        case "chdpa": $toText .= date("D, j M Y"); break;
                        case "chtime": $toText .= date("H:i:s"); break;
                        // Replace some reserved characters to their html analogs.
                        case "emdash": $toText .= html_entity_decode("&mdash;"); break;
                        case "endash": $toText .= html_entity_decode("&ndash;"); break;
                        case "bullet": $toText .= html_entity_decode("&#149;"); break;
                        case "lquote": $toText .= html_entity_decode("&lsquo;"); break;
                        case "rquote": $toText .= html_entity_decode("&rsquo;"); break;
                        case "ldblquote": $toText .= html_entity_decode("&laquo;"); break;
                        case "rdblquote": $toText .= html_entity_decode("&raquo;"); break;
                        // Add all other to the control words stack. If a control word
                        // does not include parameters, set &param to true.
                        default:
                            $stack[$j][strtolower($word)] = empty($param) ? true : $param;
                        break;
                    }
                    // Add data to the output stream if required.
                    if (rtf_isPlainText($stack[$j]))
                        $document .= $toText;
                }

                $i++;
            break;
            // If we read the opening brace {, then new subgroup starts and we add
            // new array stack element and write the data from previous stack element to it.
            case "{":
                array_push($stack, $stack[$j++]);
            break;
            // If we read the closing brace }, then we reach the end of subgroup and should remove
            // the last stack element.
            case "}":
                array_pop($stack);
                $j--;
            break;
            // Skip “trash”.
            case '\0': case '\r': case '\f': case '\n': break;
            // Add other data to the output stream if required.
            default:
                if (rtf_isPlainText($stack[$j]))
                    $document .= $c;
            break;
        }
    }
    // Return result.
    return $document;
}

echo rtf2text("{\rtf1\deff0{\fonttbl{\f0 Calibri;}{\f1 Tahoma;}}{\colortbl ;\red0\green0\blue255 ;}{\*\defchp \fs22}{\stylesheet {\ql\fs22 Normal;}{\*\cs1\fs22 Default Paragraph Font;}{\*\cs2\sbasedon1\fs22 Line Number;}{\*\cs3\ul\fs22\cf1 Hyperlink;}{\*\ts4\tsrowd\fs22\ql\tscellpaddfl3\tscellpaddl108\tscellpaddfb3\tscellpaddfr3\tscellpaddr108\tscellpaddft3\tsvertalt\cltxlrtb Normal Table;}{\*\ts5\tsrowd\sbasedon4\fs22\ql\trbrdrt\brdrs\brdrw10\trbrdrl\brdrs\brdrw10\trbrdrb\brdrs\brdrw10\trbrdrr\brdrs\brdrw10\trbrdrh\brdrs\brdrw10\trbrdrv\brdrs\brdrw10\tscellpaddfl3\tscellpaddl108\tscellpaddfr3\tscellpaddr108\tsvertalt\cltxlrtb Table Simple 1;}}{\*\listoverridetable}{\info{\creatim\yr2017\mo10\dy18\hr12\min24}{\version1}}\nouicompat\splytwnine\htmautsp\sectd\pard\plain\ql{\lang3082\langfe3082\f1\fs16\cf1 se creo para prueba test}\f1\fs16\cf1\par{\*\themedata
504b03041400020008000000210201159325ae000000270100000b0000005f72656c732f2e72656c738d8f310ec2300c45af1279a76e1910424dbbb07460415c204a9d34a24da224457036068ec415c8d82206164b5ffe7efffbfd7cd5ed7d1ad98d4234
ce72a88a121859e97a63358739a9cd1edaa63ed3285276c4c1f8c8f2898d1c8694fc0131ca8126110be7c9e68d72611229cba0d10b79159a705b963b0c4b06ac99aceb3984aeaf805d1e9efe613ba58ca4a393f34436fd88f87264b2089a12879499848b
791236b70c454e00864d8dab779b0f504b0304140002000800000021027f624aeb730000007b0000001c0000007468656d652f7468656d652f7468656d654d616e616765722e786d6c0d8cc10d83300c005789fc2f4e7954554460820e61810991888392
b465b73e3a5257a89fa7d3ddeff31da633ede6c5a5c62c1eae9d05c332e7254af0f06cebe50ed338906b1b277e9050e062b491eac8c3d6dae110ebac966a970f16756b2e899a6209b8147aeb2bedd85b7bc34451c0e0f807504b03041400020008000000
21020a5d705ea60000000c010000270000007468656d652f7468656d652f5f72656c732f7468656d654d616e616765722e786d6c2e72656c738dcf3d0ec2300c05e0ab44dea95b068450d32e2c5d11178852278d687e94a408cec6c091b8021113951818
9fecf7597e3d9e6d7fb333bb524cc63b0e4d55032327fd689ce6b064b5d943dfb5279a452e1b693221b1527189c39473382026399115a9f2815c99281fadc825468d41c88bd084dbbade61fc36606db261e41087b10176be07fac7f64a1949472f174b2e
ff3881b974a980226aca1c3eb1a90a030cbb16573f756f504b030414000200080000002102948fae26af050000c51b0000160000007468656d652f7468656d652f7468656d65312e786d6ced594f8fdb4414ff2a23df5bc7899d66574dab4d3669a1dd76
b51b8a7a9c38137b9ab1c79a99ec3637d41e919010057141e2c60101955a8903457c98852228d27e059e1d271e27e3ed6ebb15203687c433febdf77b7fe63dcf38c73ffd72f5fac388a1032224e571db722ed72c44629f8f681cb4ada91a5f6a59d7af5d
c59b2a241141008ee5266e5ba152c9a66d4b1fa6b1bccc1312c3bd311711563014813d12f8109444ccaed76a4d3bc234b6508c23d2b6ee8ec7d4276890aab496ca7b0cbe6225d3099f897d3f63d42532ec68e2a43f7226bb4ca003ccda16f08cf8e1803c
5416b2af5db59720a62ab00b5c0e184dea194e04c325d0e9bb1b57b60b85f5b9c27560afd7ebf69c426386c0be0fbe386b60b7df723a4bad1a6a7eb9aebd5bf36aee8a80c6d05813d8e8743ade4659a05108b86b02ad5ad3ddaa9705dc42c05bf7a1b3d5
ed36cb025e21d05c13e85fd968ba2b02192a64349eacc16bf0e9f717f02566ccd94d23be05f8566d812f60b6b68ee60a6255b5aa22fc808b3e00b22c634563a4660919631f705d1c0d05c51903de2458bb95cff9727d2ea543d21734516debfd04c3f22f
30c72fbe3b7ef10c1dbf787af4e8f9d1a31f8f1e3f3e7af48349f2268e035df2d5379ffef5d547e8cf675fbf7af2798580d4057efbfee35f7ffeac02a974e4cb2f9efefefce9cb2f3ff9e3db2726fc96c0431d3fa01191e80e39447b3c4afd335090a138
a3c820c4b4248243809a903d15969077669819811d528ee13d015dc088bc317d50b2773f1453454dc85b615442ee70ce3a5c987dba95d1693e4de3a0825f4c75e01ec60746faee4a967bd3045636352aed86a464ea2e83c4e380c444a1f41e9f106292bb
4f6929be3bd4175cf2b142f729ea606a0ecc800e9559ea268d204133a38d90f5528476eea10e6746826d72508642856066544a58299a37f054e1c86c358e980ebd8d556834747f26fc52e0a582a4078471d41b11298d4277c5ac64f22d0c2dcabc0276d8
2c2a4385a21323f436e65c876ef34937c45162b69bc6a10e7e4f4e60c562b4cb95d90e5eae99740c09c17175e6ef51a2ce58ec1fd020342f96f4ce54186b84f0728dced8189378f10028b5f288c627f57546a1b15ff4f595bebe050f3b633dad76f34ae0
7fb4876fe369bc4bd23ab968e1172dfca2859f50e1efa27117bddad677eb999ea872eb3ea68cedab1923b765d6e525b838eac36436c88496478524844b6b4e57c2050267d74870f52155e17e8813a071328640e6aa0389122ee1846255eace8eb3149cce
e6bcf42c6365919058edf0d17cba515bccdb9a9a6c14489da8912a382d59e3cadb913973e029d91ccfcce69dc8666bd184fa41387dcde034eb736a58299891511af7b982455ace3d4532c42392e7c8313ae2344e19b6d6eba3a6b16d34de8eed3449d2e9
dc0a3aef1cb2545bcb92bd5e8e2c2e8fd02158e5d53d0bf938695b63d894c16594803e99b62bcc82b86df92a77e5b5c5bceab079593ab54a874b1489906a1bcb702e95ddca85585cd85ff7dc340ee7e380a11b9dce8a46cbf907adb057534bc663e2ab8a
996298dfe35345c47e383a444336157b18ec76e7ab6b44253c2bea8b81800a75f38557aefcbc0a565f1de5d5815912e2bc27b5b4dccfe1d9f5d2866ca4996757d8fe86ae34ced115efffeb4aba7261c3db18652733d806088cd235dab6b85021872e9484
d4ef0bd838645c601782b2484d422c7dbb9dda4a0e8abe35d7316f7241a8f6688004854ea74241c8aecafd7c8d32a7ae3f5f178af23eb3345726f3df2139206c90566f33f5df42e1a29be481c870ab49b34dd5350cfaffe29d8f5bb1f339797b5010b967
d98bb85ad3d71e051b6f67c2191fb575b3c775efd48fda040e2c28fd82c64d85cf8afded80ef41f6d172478960215e6ae5e5b79c1c82cd2dcdb954d5bbdd4615296855e4fb3c379f5ab01b15c13e99eecd83ed1962ed9d1c6a7bbd446ded20938dd6fef8
e2c307c0bd0d07a42953327f31f5108ea7ddc53f19a0283f2f65c2d7fe06504b030414000200080000002102e9278183ee00000010020000130000005b436f6e74656e745f54797065735d2e786d6cad914d4ec3301085af62795bc54e5920849274012c
f959708191334e2ce21fd993aa9c8d0547e20a4cd382102a48486c2cd9efcdfb66c66f2fafcd66e727b1c55c5c0cad5cab5a0a0c26f62e0cad9cc9561772d3358fcf098b606b28ad1c89d2a5d6c58ce8a1a89830b06263f6407ccd834e609e60407d56d7
e7dac44018a8a27d86ec9a6bb4304f246e76fc7cc0669c8a145707e39ed54a486972068875bd0dfd374a752428ae5c3c6574a9acd820853e8958a41f091f85f7bc89ec7a140f90e90e3cdb34f198f8f55cabdfc34eb41bad7506fb6866cf256a8959fd09
7a0b81e7cdff833e867d76a097ffedde01504b010214001400020008000000210201159325ae000000270100000b00000000000000000000000000000000005f72656c732f2e72656c73504b01021400140002000800000021027f624aeb730000007b00
00001c00000000000000000000000000d70000007468656d652f7468656d652f7468656d654d616e616765722e786d6c504b01021400140002000800000021020a5d705ea60000000c0100002700000000000000000000000000840100007468656d652f
7468656d652f5f72656c732f7468656d654d616e616765722e786d6c2e72656c73504b0102140014000200080000002102948fae26af050000c51b000016000000000000000000000000006f0200007468656d652f7468656d652f7468656d65312e786d
6c504b0102140014000200080000002102e9278183ee000000100200001300000000000000000000000000520800005b436f6e74656e745f54797065735d2e786d6c504b050600000000050005005d010000710900000000}{\*\colorschememapping 3c
3f786d6c2076657273696f6e3d22312e302220656e636f64696e673d227574662d38223f3e3c613a636c724d617020786d6c6e733a613d22687474703a2f2f736368656d61732e6f70656e786d6c666f726d6174732e6f72672f64726177696e676d6c2f
323030362f6d61696e22206267313d226c743122207478313d22646b3122206267323d226c743222207478323d22646b322220616363656e74313d22616363656e74312220616363656e74323d22616363656e74322220616363656e74333d2261636365
6e74332220616363656e74343d22616363656e74342220616363656e74353d22616363656e74352220616363656e74363d22616363656e74362220686c696e6b3d22686c696e6b2220666f6c486c696e6b3d22666f6c486c696e6b22202f3e}}")

?>
