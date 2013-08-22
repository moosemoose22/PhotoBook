DELIMITER // 
CREATE PROCEDURE GetLinks(IN BookID int, IN PageNum int)
BEGIN
	DECLARE prevVal int;
	DECLARE nextVal int;
	SELECT BookPageNum into prevVal
	FROM BookPages
	WHERE BookPageNum < PageNum
	AND BookID = BookID
	ORDER BY BookPageNum DESC LIMIT 1;
	
	SELECT BookPageNum into nextVal
	FROM BookPages
	WHERE BookPageNum > PageNum
	AND BookID = BookID
	ORDER BY BookPageNum ASC LIMIT 1;
	
	select 'prev' as linkname, prevVal as linkval
	union
	select 'next' as linkname, nextVal as linkval;
END // 
DELIMITER ; 


DELIMITER // 
CREATE PROCEDURE GetMaxPagenum(IN inputBookID int)
BEGIN
	DECLARE maxPagenum int;
	SELECT MAX(BookPageNum) into maxPagenum
	FROM BookPages
	WHERE BookID = inputBookID;
	
	if maxPagenum IS NULL THEN
		SET maxPagenum = 1;
	ELSE
		SET maxPagenum = maxPagenum + 1;
	END IF;
	
	INSERT INTO BookPages(BookID, BookPageNum)
	VALUES (inputBookID, maxPagenum);
END // 
DELIMITER ; 