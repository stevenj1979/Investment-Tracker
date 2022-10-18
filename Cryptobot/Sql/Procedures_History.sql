DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `writeCoinTableToHistory`(IN `nID` INT, IN `nSymbol` VARCHAR(100), IN `nName` VARCHAR(250), IN `nBaseCurrency` VARCHAR(100), IN `nBuyCoin` INT, IN `nCMCID` INT, IN `nSecondstoUpdate` INT, IN `nImage` VARCHAR(250), IN `nMinTradeSize` DECIMAL(20,14), IN `nCoinPrecision` DECIMAL(20,15), IN `nDoNotBuy` INT)
    MODIFIES SQL DATA
BEGIN

IF NOT EXISTS (SELECT `ID` FROM `Coin` WHERE `ID` = nID) THEN
	INSERT INTO `Coin`(`ID`) VALUES (nID);
End if;

UPDATE `Coin` SET `ID`=nID,`Symbol`=nSymbol,`Name`=nName,`BaseCurrency`=nBaseCurrency,`BuyCoin`=nBuyCoin,`CMCID`=nCMCID,`SecondstoUpdate`=nSecondstoUpdate,`Image`=nImage,`MinTradeSize`=nMinTradeSize,`CoinPrecision`=nCoinPrecision, `DoNotBuy` = nDoNotBuy WHERE `ID` = nID;

END$$
DELIMITER ;



DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` PROCEDURE `NewUpdatePriceHistory`(IN `coin_ID` INT, IN `bit_Price` DECIMAL(20,16), IN `base_Currency` VARCHAR(50), IN `coinPrice_HistoryTime` DATETIME, IN `Hr1_Pct` DECIMAL(20,16), IN `Hr24_Pct` DECIMAL(20,16), IN `D7_Pct` DECIMAL(20,16))
    MODIFIES SQL DATA
BEGIN
Declare Date_ID INT;

If NOT EXISTS (SELECT `ID` FROM `PriceHistoryDate` WHERE `PriceDateTime` = coinPrice_HistoryTime) THEN
  INSERT INTO `PriceHistoryDate` (`PriceDateTime`) VALUES (coinPrice_HistoryTime);
End if;

SELECT `ID` into Date_ID FROM `PriceHistoryDate` WHERE `PriceDateTime` = coinPrice_HistoryTime;

INSERT INTO `PriceHistory`( `CoinID`, `Price`,  `BaseCurrency`, `Hr1Pct`, `Hr24Pct`, `D7Pct`, `PriceDateTimeID`) VALUES (coin_ID,bit_Price,base_Currency,Hr1_Pct,Hr24_Pct,D7_Pct,Date_ID);

END$$
DELIMITER ;
