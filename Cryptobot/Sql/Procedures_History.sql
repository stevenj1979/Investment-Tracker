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
