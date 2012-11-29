<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template match="/">       
        <h2>Cronjob Status</h2>
        <h3><xsl:value-of select="version" /></h3>
        <table border="1">
            <tr bgcolor="#9acd32">
                <th>Job</th>
                <th>Running</th>
                <th>Registered</th>
                <th>Lastrun</th>
            </tr>
            <xsl:for-each select="cronjobs/cronjob">
                <tr>
                    <td>
                        <xsl:value-of select="@key"/>
                    </td>
                    <td>
                        <xsl:value-of select="running"/>
                    </td>
                    <td>
                        <xsl:value-of select="registered"/>
                    </td>
                    <td>
                        <xsl:value-of select="lastrun"/>
                    </td>
                </tr>
            </xsl:for-each>
        </table>
    </xsl:template>
</xsl:stylesheet>

