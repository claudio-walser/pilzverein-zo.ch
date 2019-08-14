import {$get} from 'plow-js';
import {createSelector, defaultMemoize} from 'reselect';
import {GlobalState} from '@neos-project/neos-ui-redux-store/src/System';
import {parentNodeContextPath} from './helpers';
import {NodeContextPath, NodeMap, Node, NodeTypeName, ClipboardMode, NodeTypesRegistry} from '@neos-project/neos-ts-interfaces';

export const nodesByContextPathSelector = (state: GlobalState) => $get(['cr', 'nodes', 'byContextPath'], state);
export const siteNodeContextPathSelector = (state: GlobalState) => $get(['cr', 'nodes', 'siteNode'], state);
export const documentNodeContextPathSelector = (state: GlobalState) => $get(['cr', 'nodes', 'documentNode'], state);
// This is internal, as in most cases you want `focusedNodePathSelector`, which is able to fallback to documentNode, when no node is focused
const _focusedNodeContextPathSelector = (state: GlobalState) => $get(['cr', 'nodes', 'focused', 'contextPath'], state);

export const isDocumentNodeSelectedSelector = createSelector(
    [
        _focusedNodeContextPathSelector,
        documentNodeContextPathSelector
    ],
    (focused, currentContentCanvasContextPath) => {
        return !focused || (focused === currentContentCanvasContextPath);
    }
);

export const hasFocusedContentNode = createSelector(
    [
        _focusedNodeContextPathSelector,
        documentNodeContextPathSelector
    ],
    (focused, currentContentCanvasContextPath) => {
        return Boolean(focused && (focused !== currentContentCanvasContextPath));
    }
);

export const nodeByContextPath = (state: GlobalState) => (contextPath: NodeContextPath) =>
    $get(['cr', 'nodes', 'byContextPath', contextPath], state);

export const makeGetDocumentNodes = (nodeTypesRegistry: NodeTypesRegistry) => createSelector(
    [
        nodesByContextPathSelector
    ],
    nodesMap => {
        const documentRole = nodeTypesRegistry.getRole('document');
        if (!documentRole) {
            throw new Error('Document role is not loaded!');
        }
        const documentSubNodeTypes = nodeTypesRegistry.getSubTypesOf(documentRole);

        const result: NodeMap = {};
        Object.keys(nodesMap).forEach(contextPath => {
            const node = nodesMap[contextPath];
            if (!node) {
                throw new Error('This error should never be thrown, it\'s a way to fool TypeScript');
            }
            if (documentSubNodeTypes.includes(node.nodeType)) {
                result[contextPath] = node;
            }
        });
        return result;
    }
);

export const makeGetNodeByContextPathSelector = (contextPath: NodeContextPath) => createSelector(
    [
        (state: GlobalState) => $get(['cr', 'nodes', 'byContextPath', contextPath], state)
    ],
    node => node
);

export const makeHasChildrenSelector = (allowedNodeTypes: NodeTypeName[]) => createSelector(
    [
        (state: GlobalState, contextPath: NodeContextPath) => $get(['cr', 'nodes', 'byContextPath', contextPath], state)
    ],
    node => (node && node.children || []).some(
        childNodeEnvelope => allowedNodeTypes.includes(childNodeEnvelope.nodeType)
    )
);

export const makeChildrenOfSelector = (allowedNodeTypes: NodeTypeName[]) => createSelector(
    [
        (state: GlobalState, contextPath: NodeContextPath) => $get(['cr', 'nodes', 'byContextPath', contextPath], state),
        nodesByContextPathSelector
    ],
    (node, nodesByContextPath: NodeMap) => (node && node.children || [])
    .filter(
        childNodeEnvelope => {
            const nodeType = childNodeEnvelope.nodeType;
            return allowedNodeTypes.includes(nodeType) || nodeType === 'Neos.Neos:FallbackNode';
        }
    )
    .map(
        childNodeEnvelope => {
            return nodesByContextPath[childNodeEnvelope.contextPath];
        }
    )
);

export const siteNodeSelector = createSelector(
    [
        siteNodeContextPathSelector,
        nodesByContextPathSelector
    ],
    (siteNodeContextPath, nodesByContextPath) => {
        if (siteNodeContextPath) {
            return nodesByContextPath[siteNodeContextPath] || null;
        }
        return null;
    }
);

export const documentNodeSelector = createSelector(
    [
        documentNodeContextPathSelector,
        nodesByContextPathSelector
    ],
    (documentNodeContextPath, nodesByContextPath) => {
        if (documentNodeContextPath) {
            return nodesByContextPath[documentNodeContextPath] || null;
        }
        return null;
    }
);

export const byContextPathSelector = defaultMemoize(
    (contextPath: NodeContextPath) => createSelector(
        [
            nodeByContextPath
        ],
        getNodeByContextPath => getNodeByContextPath(contextPath)
    )
);

export const parentNodeSelector = (state: GlobalState) => (baseNode: Node) => {
    const parent = parentNodeContextPath(baseNode.contextPath);
    if (parent !== null) {
        return byContextPathSelector(parent)(state);
    }
    return null;
};

export const grandParentNodeSelector = (state: GlobalState) => (baseNode: Node) => {
    const parent = parentNodeContextPath(baseNode.contextPath);
    if (parent !== null) {
        const grandParent = parentNodeContextPath(parent);
        if (grandParent !== null) {
            byContextPathSelector(grandParent)(state);
        }
    }
    return null;
};

export const focusedNodePathSelector = createSelector(
    [
        _focusedNodeContextPathSelector,
        documentNodeContextPathSelector
    ],
    (focused, currentContentCanvasContextPath) => {
        return focused || currentContentCanvasContextPath;
    }
);

export const focusedSelector = createSelector(
    [
        focusedNodePathSelector,
        nodeByContextPath
    ],
    (focusedNodePath, getNodeByContextPath) => {
        if (focusedNodePath !== null) {
            return getNodeByContextPath(focusedNodePath) || null;
        }
        return null;
    }
);

export const focusedNodeTypeSelector = createSelector(
    [
        focusedSelector
    ],
    focused => focused && focused.nodeType
);

export const focusedNodeIdentifierSelector = createSelector(
    [
        focusedSelector
    ],
    focused => focused && focused.identifier
);

export const focusedParentSelector = createSelector(
    [
        focusedSelector,
        state => state
    ],
    (focusedNode, state) => {
        if (!focusedNode) {
            return null;
        }

        return parentNodeSelector(state)(focusedNode);
    }
);

export const focusedGrandParentSelector = createSelector(
    [
        focusedParentSelector,
        state => state
    ],
    (focusedParentNode, state) => {
        if (!focusedParentNode) {
            return null;
        }

        return parentNodeSelector(state)(focusedParentNode);
    }
);

export const clipboardNodeContextPathSelector = createSelector(
    [
        (state: GlobalState) => $get(['cr', 'nodes', 'clipboard'], state)
    ],
    clipboardNodeContextPath => clipboardNodeContextPath
);

export const clipboardIsEmptySelector = createSelector(
    [
        clipboardNodeContextPathSelector
    ],
    clipboardNodePath => Boolean(clipboardNodePath)
);

// TODO: deprecate
export const getPathInNode = (state: GlobalState, contextPath: NodeContextPath, propertyPath: any) => {
    const node = $get(['cr', 'nodes', 'byContextPath', contextPath], state);

    return $get(propertyPath, node);
};

export const makeGetAllowedChildNodeTypesSelector = (nodeTypesRegistry: NodeTypesRegistry, elevator: (id: string) => string | null = id => id) => createSelector(
    [
        (state: GlobalState, {reference}: {reference: NodeContextPath | null, role: string}) => {
            if (reference === null) {
                return null;
            }
            const elevatedReference = elevator(reference);
            if (elevatedReference) {
                return $get(['cr', 'nodes', 'byContextPath', elevatedReference], state) || null;
            }
            return null;
        },
        (state: GlobalState, {reference}: {reference: NodeContextPath | null, role: string}) => {
            if (reference === null) {
                return null;
            }
            const parentReference = parentNodeContextPath(reference);
            if (parentReference !== null) {
                const elevatedReferenceParent = elevator(parentReference);
                if (elevatedReferenceParent !== null) {
                    return $get(['cr', 'nodes', 'byContextPath', elevatedReferenceParent], state) || null;
                }
            }
            return null;
        },
        (_: GlobalState, {role}: {reference: NodeContextPath | null, role: string, subject: NodeContextPath | null}) => role
    ],
    (referenceNode, referenceParentNode, role) => {
        if (referenceNode === null || (referenceNode.policy && referenceNode.policy.canEdit === false)) {
            return [];
        }
        const isSubjectNodeAutocreated = referenceNode.isAutoCreated;
        const referenceParentName = referenceNode.name;
        const referenceParentNodeType = referenceNode.nodeType;
        const referenceGrandParentNodeType = referenceParentNode ? referenceParentNode.nodeType : null;
        return nodeTypesRegistry
            .getAllowedNodeTypesTakingAutoCreatedIntoAccount(isSubjectNodeAutocreated, referenceParentName, referenceParentNodeType, referenceGrandParentNodeType, role)
            .filter(nodeType => !(referenceNode.policy && referenceNode.policy.disallowedNodeTypes.includes(nodeType)));
    }
);

export const makeGetAllowedSiblingNodeTypesSelector = (nodeTypesRegistry: NodeTypesRegistry) =>
    makeGetAllowedChildNodeTypesSelector(nodeTypesRegistry, parentNodeContextPath);

export const makeIsAllowedToAddChildOrSiblingNodes = (nodeTypesRegistry: NodeTypesRegistry) => createSelector(
    [
        makeGetAllowedChildNodeTypesSelector(nodeTypesRegistry),
        makeGetAllowedSiblingNodeTypesSelector(nodeTypesRegistry)
    ],
    (allowedChildNodeTypes, allowedSiblingNodeTypes) =>
        Boolean(allowedChildNodeTypes.length + allowedSiblingNodeTypes.length)
);

export const makeCanBeCopiedAlongsideSelector = (nodeTypesRegistry: NodeTypesRegistry) => createSelector(
    [
        (state: GlobalState, {subject}: {subject: NodeContextPath | null}) => subject ? $get(['cr', 'nodes', 'byContextPath', subject], state) : false,
        makeGetAllowedSiblingNodeTypesSelector(nodeTypesRegistry)
    ],
    (subjectNode, allowedNodeTypes) => subjectNode ? allowedNodeTypes.includes(subjectNode.nodeType) : false
);

export const makeCanBeCopiedIntoSelector = (nodeTypesRegistry: NodeTypesRegistry) => createSelector(
    [
        (state: GlobalState, {subject}: {subject: NodeContextPath | null}) => subject ? $get(['cr', 'nodes', 'byContextPath', subject], state) : false,
        makeGetAllowedChildNodeTypesSelector(nodeTypesRegistry)
    ],
    (subjectNode, allowedNodeTypes) => subjectNode ? allowedNodeTypes.includes(subjectNode.nodeType) : false
);

export const makeCanBeMovedIntoSelector = (nodeTypesRegistry: NodeTypesRegistry) => createSelector(
    [
        makeCanBeCopiedIntoSelector(nodeTypesRegistry),
        (_, {subject, reference}: {subject: NodeContextPath | null, reference: NodeContextPath | null}) => {
            const subjectPath = subject && subject.split('@')[0];
            return subjectPath && reference ? reference.indexOf(subjectPath) === 0 : false;
        }
    ],
    (canBeInsertedInto, referenceIsDescendantOfSubject) => canBeInsertedInto && !referenceIsDescendantOfSubject
);

export const makeCanBeMovedAlongsideSelector = (nodeTypesRegistry: NodeTypesRegistry) => createSelector(
    [
        makeCanBeCopiedAlongsideSelector(nodeTypesRegistry),
        (_, {subject, reference}) => {
            if (reference === null) {
                return false;
            }
            const subjectPath = subject && subject.split('@')[0];
            const referenceParent = parentNodeContextPath(reference);
            if (referenceParent === null) {
                return false;
            }
            return subjectPath ? referenceParent.indexOf(subjectPath) === 0 : false;
        }
    ],
    (canBeInsertedInto, referenceIsDescendantOfSubject) => canBeInsertedInto && !referenceIsDescendantOfSubject
);

export const makeCanBeCopiedSelector = (nodeTypesRegistry: NodeTypesRegistry) => createSelector(
    [
        makeCanBeCopiedAlongsideSelector(nodeTypesRegistry),
        makeCanBeCopiedIntoSelector(nodeTypesRegistry)
    ],
    (canBeInsertedAlongside, canBeInsertedInto) => (canBeInsertedAlongside || canBeInsertedInto)
);

export const makeCanBeMovedSelector = (nodeTypesRegistry: NodeTypesRegistry) => createSelector(
    [
        makeCanBeMovedAlongsideSelector(nodeTypesRegistry),
        makeCanBeMovedIntoSelector(nodeTypesRegistry)
    ],
    (canBeMovedAlongside, canBeMovedInto) => (canBeMovedAlongside || canBeMovedInto)
);

export const makeCanBePastedSelector = (nodeTypesRegistry: NodeTypesRegistry) => createSelector(
    [
        makeCanBeMovedSelector(nodeTypesRegistry),
        makeCanBeCopiedSelector(nodeTypesRegistry),
        (state: GlobalState) => $get(['cr', 'nodes', 'clipboardMode'], state)
    ],
    (canBeMoved, canBeCopied, mode) => mode === ClipboardMode.COPY ? canBeCopied : canBeMoved
);

export const destructiveOperationsAreDisabledSelector = createSelector(
    [
        siteNodeContextPathSelector,
        _focusedNodeContextPathSelector,
        focusedSelector
    ],
    (siteNodeContextPath, focusedNodeContextPath, focusedNode) => {
        return (
            focusedNode === null ||
            focusedNode.isAutoCreated ||
            siteNodeContextPath === focusedNodeContextPath
        );
    }
);

export const focusedNodeParentLineSelector = createSelector(
    [
        focusedSelector,
        nodesByContextPathSelector
    ],
    (focusedNode, nodesByContextPath) => {
        const result = [focusedNode];
        let currentNode = focusedNode;

        while (currentNode) {
            const parent = parentNodeContextPath(currentNode.contextPath);
            if (parent !== null) {
                currentNode = nodesByContextPath[parent] || null;
                if (currentNode) {
                    result.push(currentNode);
                }
            }
        }

        return result;
    }
);
