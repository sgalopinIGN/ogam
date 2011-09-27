package fr.ifn.ogam.common.database.processing;

/**
 * A Process is a SQL query that is launch after the integration or the copy of data.
 */
public class ProcessData {

	/**
	 * The process identifier.
	 */
	private String processId;

	/**
	 * The step of the process : INTEGRATION or HARMONIZATION.
	 */
	private String step;

	/**
	 * The label of the process.
	 */
	private String label;

	/**
	 * A description of the process.
	 */
	private String description;

	/**
	 * The SQL string corresponding to the process.
	 */
	private String statement;

	/**
	 * @return the processId
	 */
	public String getProcessId() {
		return processId;
	}

	/**
	 * @param processId
	 *            the processId to set
	 */
	public void setProcessId(String processId) {
		this.processId = processId;
	}

	/**
	 * @return the step
	 */
	public String getStep() {
		return step;
	}

	/**
	 * @param step
	 *            the step to set
	 */
	public void setStep(String step) {
		this.step = step;
	}

	/**
	 * @return the label
	 */
	public String getLabel() {
		return label;
	}

	/**
	 * @param label
	 *            the label to set
	 */
	public void setLabel(String label) {
		this.label = label;
	}

	/**
	 * @return the description
	 */
	public String getDescription() {
		return description;
	}

	/**
	 * @param description
	 *            the description to set
	 */
	public void setDescription(String description) {
		this.description = description;
	}

	/**
	 * @return the statement
	 */
	public String getStatement() {
		return statement;
	}

	/**
	 * @param statement
	 *            the statement to set
	 */
	public void setStatement(String statement) {
		this.statement = statement;
	}

}
