<?php

namespace malkusch\bav;

/**
 * Validates a bundesbank file.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
class FileValidator
{

    const FILESIZE = 1500000; // 1.5MB

    /**
     * Validates a bundesbank file.
     *
     * @param string $file bundesbank file.
     * @throws FileValidatorException
     */
    public function validate($file)
    {
        $parser = new FileParser($file);

        // file size is normally around 3 MB. Less than 1.5 is not valid
        $size = filesize($file);
        if ($size < self::FILESIZE) {
            throw new InvalidFilesizeException(
                "Get updated BAV version: file size should be less than " . self::FILESIZE . " but was $size."
            );

        }

        // check line length
        $minLength = FileParser::SUCCESSOR_OFFSET + FileParser::SUCCESSOR_LENGTH;
        if ($parser->getLineLength() < $minLength) {
            throw new InvalidLineLengthException(
                "Get updated BAV version:"
                . " Line length shouldn't be less than $minLength but was {$parser->getLineLength()}."
            );

        }

        // rough check that line length is constant.
        if ($size % $parser->getLineLength() != 0) {
            throw new InvalidLineLengthException("Get updated BAV version: Line length is not constant.");

        }

        $firstLine = $parser->readLine(0);
        if (! preg_match("/^100000001Bundesbank/", $firstLine)) {
            throw new FieldException("Get updated BAV version: first line has unexpected content: $firstLine");

        }
    }
}
