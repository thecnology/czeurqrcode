<?php

declare(strict_types=1);

namespace thecnology\czeurqrcode;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Exception\ValidationException;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;


class QrCodeGenerator
{

    /**
     * Generates a QR code with the given data and label.
     *
     * @param string $qrData The data to encode in the QR code.
     * @param string $qrLabel The label to display below the QR code.
     * @return ResultInterface The result containing generated QR code image in PNG Image format.
     * @throws ValidationException If the QR code data is invalid.
     */
    public function getQrCode(string $qrData, string $qrLabel): ResultInterface
    {

        return (new PngWriter())->write(
            qrCode: new QrCode(
                data: $qrData,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 300,
                margin: 10,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
                foregroundColor: new Color(0, 0, 0),
                backgroundColor: new Color(255, 255, 255)
            )
            ,
            logo: null,
            label: new Label(
                text: $qrLabel,
                textColor: new Color(0, 0, 0)
            )
        );
    }

}